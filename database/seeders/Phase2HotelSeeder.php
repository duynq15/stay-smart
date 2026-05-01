<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Bổ sung 22 khách sạn để các kịch bản chatbot match strict (level 0).
 * Mỗi KS được thiết kế khớp 1 scenario cụ thể trong bộ test 20 câu.
 */
class Phase2HotelSeeder extends Seeder
{
    public function run(): void
    {
        $hotels = $this->hotelDefinitions();

        DB::transaction(function () use ($hotels) {
            foreach ($hotels as $h) {
                $hotelId = DB::table('hotels')->insertGetId([
                    'name' => $h['name'],
                    'slug' => $this->uniqueSlug($h['name']),
                    'district' => $h['district'],
                    'address' => $h['address'],
                    'lat' => $h['lat'] ?? null,
                    'lng' => $h['lng'] ?? null,
                    'stars' => $h['stars'],
                    'base_price' => $h['base_price'],
                    'rating' => $h['rating'],
                    'reviews_count' => $h['reviews_count'],
                    'description' => $h['description'],
                    'amenities' => json_encode($h['amenities'], JSON_UNESCAPED_UNICODE),
                    'phone' => $h['phone'] ?? '0241000000',
                    'email' => $h['email'] ?? 'info@example.vn',
                    'has_vr_tour' => $h['has_vr_tour'] ?? false,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Image
                DB::table('hotel_images')->insert([
                    'hotel_id' => $hotelId,
                    'url' => $h['image'],
                    'caption' => $h['name'],
                    'is_primary' => true,
                    'created_at' => now(),
                ]);

                // Rooms
                foreach ($h['rooms'] as $r) {
                    DB::table('rooms')->insert([
                        'hotel_id' => $hotelId,
                        'name' => $r['name'],
                        'description' => $r['description'],
                        'price_per_night' => $r['price'],
                        'capacity' => $r['capacity'],
                        'available_units' => $r['available_units'] ?? 5,
                        'image' => $r['image'] ?? $h['image'],
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });

        $this->command->info('Phase2HotelSeeder: thêm ' . count($hotels) . ' KS cho 20 scenario chatbot');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;
        while (DB::table('hotels')->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    private function hotelDefinitions(): array
    {
        $img = fn ($id) => "https://images.unsplash.com/photo-{$id}?w=1200&q=80";

        return [
            // Scenario 1: Bệnh viện Bạch Mai + bếp + thang máy + <500k
            [
                'name' => 'Bach Mai Family Stay',
                'district' => 'Đống Đa',
                'address' => '78 Giải Phóng, sát Bệnh viện Bạch Mai, Đống Đa, Hà Nội',
                'lat' => 20.998753, 'lng' => 105.838011,
                'stars' => 3, 'base_price' => 480000, 'rating' => 4.4, 'reviews_count' => 287,
                'description' => 'Homestay 2 phút đi bộ tới Bệnh viện Bạch Mai, có bếp chung tiện nấu cháo cho người nhà.',
                'amenities' => ['wifi', 'elevator', 'kitchen', 'laundry', 'parking', 'security_24_7'],
                'image' => $img('1554995207-c18c203602cb'),
                'rooms' => [
                    ['name' => 'Phòng đôi tiêu chuẩn', 'description' => '20m² · 1 giường đôi · cửa sổ', 'price' => 480000, 'capacity' => 2],
                    ['name' => 'Phòng gia đình 3 người', 'description' => '28m² · 1 giường đôi + 1 đơn', 'price' => 580000, 'capacity' => 3],
                ],
            ],

            // Scenario 2: Đống Đa căn hộ mini + máy giặt + ~700k/đêm
            [
                'name' => 'Dong Da Mini Apartment',
                'district' => 'Đống Đa',
                'address' => '15 Phương Mai, Đống Đa, Hà Nội',
                'lat' => 21.000234, 'lng' => 105.836501,
                'stars' => 3, 'base_price' => 700000, 'rating' => 4.6, 'reviews_count' => 412,
                'description' => 'Căn hộ mini đầy đủ tiện nghi, có máy giặt riêng và sân phơi đồ, phù hợp ở dài ngày.',
                'amenities' => ['wifi', 'elevator', 'kitchen', 'laundry', 'washer', 'workspace', 'parking', 'air_purifier'],
                'image' => $img('1522708323590-d24dbb6b0267'),
                'rooms' => [
                    ['name' => 'Studio Apartment', 'description' => '32m² · giường queen · bếp riêng + máy giặt', 'price' => 700000, 'capacity' => 2],
                    ['name' => 'One-Bedroom Apartment', 'description' => '45m² · 1 phòng ngủ riêng · bếp đầy đủ', 'price' => 950000, 'capacity' => 3],
                ],
            ],

            // Scenario 3: Kim Mã (Ba Đình) + phòng tắm kính + 1,2 triệu
            [
                'name' => 'Kim Ma Crystal Boutique',
                'district' => 'Ba Đình',
                'address' => '88 Kim Mã, Ba Đình, Hà Nội',
                'lat' => 21.030147, 'lng' => 105.823756,
                'stars' => 4, 'base_price' => 1200000, 'rating' => 4.7, 'reviews_count' => 856,
                'description' => 'Boutique hotel hiện đại với thiết kế phòng tắm kính trong suốt (open bathroom) độc đáo.',
                'amenities' => ['wifi', 'elevator', 'breakfast', 'restaurant', 'concierge', 'open_bathroom', 'large_window', 'soundproof', 'smart_tv'],
                'image' => $img('1582719478250-c89cae4dc85b'),
                'has_vr_tour' => true,
                'rooms' => [
                    ['name' => 'Crystal Bath Double', 'description' => '28m² · giường đôi · phòng tắm kính view phố', 'price' => 1200000, 'capacity' => 2],
                    ['name' => 'Crystal Bath Suite', 'description' => '40m² · suite · bồn tắm kính view phố', 'price' => 1850000, 'capacity' => 2],
                ],
            ],

            // Scenario 4: Ba Đình + an ninh nữ + concierge + thẻ từ TM + <800k
            [
                'name' => 'Ba Dinh Safe Stay for Women',
                'district' => 'Ba Đình',
                'address' => '125 Đội Cấn, Ba Đình, Hà Nội',
                'lat' => 21.038921, 'lng' => 105.826134,
                'stars' => 3, 'base_price' => 750000, 'rating' => 4.8, 'reviews_count' => 1023,
                'description' => 'KS được phụ nữ đi một mình tin chọn — lễ tân nữ trực 24/7, thang máy thẻ từ, camera an ninh đầy đủ.',
                'amenities' => ['wifi', 'elevator', 'concierge', 'security_24_7', 'breakfast', 'laundry'],
                'image' => $img('1551776235-dde6d4829808'),
                'rooms' => [
                    ['name' => 'Lady Solo Room', 'description' => '20m² · 1 giường queen · phòng tầng nữ', 'price' => 750000, 'capacity' => 1],
                    ['name' => 'Twin Friends', 'description' => '25m² · 2 giường đơn · gói bạn nữ', 'price' => 850000, 'capacity' => 2],
                ],
            ],

            // Scenario 5: Smart TV Netflix + 1 triệu
            [
                'name' => 'Cinema Stay Hanoi',
                'district' => 'Hai Bà Trưng',
                'address' => '46 Bà Triệu, Hai Bà Trưng, Hà Nội',
                'lat' => 21.018567, 'lng' => 105.851982,
                'stars' => 3, 'base_price' => 950000, 'rating' => 4.5, 'reviews_count' => 567,
                'description' => 'Mỗi phòng có Smart TV 55" với Netflix/Disney+ pre-installed. Lý tưởng cho cuối tuần xem phim.',
                'amenities' => ['wifi', 'smart_tv', 'elevator', 'breakfast', 'soundproof', 'restaurant'],
                'image' => $img('1591088398332-8a7791972843'),
                'rooms' => [
                    ['name' => 'Cinema Double', 'description' => '24m² · TV 55" Netflix · giường đôi', 'price' => 950000, 'capacity' => 2],
                    ['name' => 'Cinema Family', 'description' => '32m² · 2 TV · 2 giường đôi', 'price' => 1450000, 'capacity' => 4],
                ],
            ],

            // Scenario 6: Hà Đông + phòng rộng + 4-sao quality + <800k
            [
                'name' => 'Ha Dong Spacious Hotel',
                'district' => 'Hà Đông',
                'address' => '99 Quang Trung, Hà Đông, Hà Nội',
                'lat' => 20.967234, 'lng' => 105.778654,
                'stars' => 4, 'base_price' => 780000, 'rating' => 4.5, 'reviews_count' => 892,
                'description' => 'KS 4 sao mới khai trương ở Hà Đông, phòng tiêu chuẩn 35m² rộng rãi, sạch sẽ — giá vô cùng hợp lý.',
                'amenities' => ['wifi', 'elevator', 'breakfast', 'parking', 'gym', 'restaurant', 'large_room'],
                'image' => $img('1611892440504-42a792e24d32'),
                'rooms' => [
                    ['name' => 'Spacious Deluxe', 'description' => '35m² · giường king · phòng tắm riêng', 'price' => 780000, 'capacity' => 2],
                    ['name' => 'Family Suite', 'description' => '50m² · 2 giường đôi · view sân vườn', 'price' => 1180000, 'capacity' => 4],
                ],
            ],

            // Scenario 7: Khâm Thiên (Đống Đa) + 3 người + 2 giường đôi + 900k
            [
                'name' => 'Kham Thien Family Inn',
                'district' => 'Đống Đa',
                'address' => '67 Khâm Thiên, Đống Đa, Hà Nội',
                'lat' => 21.018345, 'lng' => 105.840712,
                'stars' => 3, 'base_price' => 880000, 'rating' => 4.4, 'reviews_count' => 423,
                'description' => 'KS gia đình giữa phố Khâm Thiên, có loại phòng 2 giường đôi rộng cho nhóm 3-4 người.',
                'amenities' => ['wifi', 'elevator', 'breakfast', 'parking', 'family_room', 'large_room'],
                'image' => $img('1582719508461-905c673771fd'),
                'rooms' => [
                    ['name' => 'Twin Double Family', 'description' => '32m² · 2 giường đôi 1m6 · cửa sổ phố', 'price' => 880000, 'capacity' => 4],
                    ['name' => 'Triple Standard', 'description' => '28m² · 1 giường đôi + 1 đơn', 'price' => 750000, 'capacity' => 3],
                ],
            ],

            // Scenario 8: Nhà Thờ Lớn (Hoàn Kiếm) + cửa sổ lớn + 1 triệu
            [
                'name' => 'Cathedral Window Hotel',
                'district' => 'Hoàn Kiếm',
                'address' => '12 Lý Quốc Sư, gần Nhà Thờ Lớn, Hoàn Kiếm, Hà Nội',
                'lat' => 21.029876, 'lng' => 105.849234,
                'stars' => 3, 'base_price' => 990000, 'rating' => 4.6, 'reviews_count' => 1145,
                'description' => 'Boutique nhỏ ngay phố Lý Quốc Sư, mọi phòng đều có cửa sổ lớn nhìn ra phố cổ — không bí bách.',
                'amenities' => ['wifi', 'elevator', 'breakfast', 'large_window', 'street_view', 'soundproof'],
                'image' => $img('1564501049412-61c2a3083791'),
                'rooms' => [
                    ['name' => 'Window View Double', 'description' => '22m² · cửa sổ 2m × 1.5m · giường đôi', 'price' => 990000, 'capacity' => 2],
                    ['name' => 'Premium Window Suite', 'description' => '32m² · 2 cửa sổ lớn · view nhà thờ', 'price' => 1450000, 'capacity' => 2],
                ],
            ],

            // Scenario 9: Phố cổ + 4 người + thang máy + <1.5tr
            [
                'name' => 'Old Quarter Family Suites',
                'district' => 'Hoàn Kiếm',
                'address' => '38 Hàng Hành, Hoàn Kiếm, Hà Nội',
                'lat' => 21.031876, 'lng' => 105.851234,
                'stars' => 4, 'base_price' => 1400000, 'rating' => 4.7, 'reviews_count' => 1567,
                'description' => 'KS gia đình trong phố cổ, có thang máy hỗ trợ người già và phòng family 4 người.',
                'amenities' => ['wifi', 'elevator', 'breakfast', 'concierge', 'family_room', 'restaurant', 'large_room'],
                'image' => $img('1455587734955-081b22074882'),
                'has_vr_tour' => true,
                'rooms' => [
                    ['name' => 'Family Quad Suite', 'description' => '45m² · 2 phòng ngủ · phù hợp 4 người', 'price' => 1400000, 'capacity' => 4],
                    ['name' => 'Grand Family Suite', 'description' => '60m² · 2 giường đôi + sofa', 'price' => 1850000, 'capacity' => 5],
                ],
            ],

            // Scenario 10: Tạ Hiện + sạch + <600k
            [
                'name' => 'Ta Hien Backpacker Inn',
                'district' => 'Hoàn Kiếm',
                'address' => '8 Tạ Hiện, Hoàn Kiếm, Hà Nội',
                'lat' => 21.034521, 'lng' => 105.851765,
                'stars' => 2, 'base_price' => 580000, 'rating' => 4.3, 'reviews_count' => 678,
                'description' => 'Inn sạch giá rẻ ngay phố Tạ Hiện. Chỉ cần chỗ ngủ, đi chơi cả ngày.',
                'amenities' => ['wifi', 'elevator', 'laundry'],
                'image' => $img('1631049307264-da0ec9d70304'),
                'rooms' => [
                    ['name' => 'Standard Single', 'description' => '14m² · giường đơn · sạch sẽ', 'price' => 380000, 'capacity' => 1],
                    ['name' => 'Standard Double', 'description' => '18m² · giường đôi', 'price' => 580000, 'capacity' => 2],
                ],
            ],

            // Scenario 11: Hàng Bông + chỗ gửi ô tô + 1.2tr
            [
                'name' => 'Hang Bong Garage Hotel',
                'district' => 'Hoàn Kiếm',
                'address' => '156 Hàng Bông, Hoàn Kiếm, Hà Nội',
                'lat' => 21.030145, 'lng' => 105.844567,
                'stars' => 3, 'base_price' => 1200000, 'rating' => 4.5, 'reviews_count' => 543,
                'description' => 'KS hiếm hoi trong phố cổ có gara ô tô riêng cho khách. Tiện cho người đi xe nhà.',
                'amenities' => ['wifi', 'elevator', 'breakfast', 'parking', 'concierge', 'restaurant'],
                'image' => $img('1542314831-068cd1dbfeeb'),
                'rooms' => [
                    ['name' => 'Deluxe Double', 'description' => '24m² · giường đôi · vé gửi xe miễn phí', 'price' => 1200000, 'capacity' => 2],
                    ['name' => 'Deluxe Twin', 'description' => '26m² · 2 giường đơn · vé gửi xe miễn phí', 'price' => 1250000, 'capacity' => 2],
                ],
            ],

            // Scenario 12: Phố đi bộ + cách âm
            [
                'name' => 'Quiet Haven Old Quarter',
                'district' => 'Hoàn Kiếm',
                'address' => '23 Hàng Trống, sát phố đi bộ, Hoàn Kiếm, Hà Nội',
                'lat' => 21.030567, 'lng' => 105.851987,
                'stars' => 4, 'base_price' => 1100000, 'rating' => 4.7, 'reviews_count' => 891,
                'description' => 'Nằm sát phố đi bộ nhưng cửa cách âm cấp cao, tối ngủ rất yên — không nghe tiếng nhạc đường phố.',
                'amenities' => ['wifi', 'elevator', 'breakfast', 'soundproof', 'concierge', 'restaurant'],
                'image' => $img('1551882547-ff40c63fe5fa'),
                'rooms' => [
                    ['name' => 'Soundproof Deluxe', 'description' => '24m² · cửa cách âm · giường king', 'price' => 1100000, 'capacity' => 2],
                ],
            ],

            // Scenario 13: Quảng An + view hồ + bếp + 1.2tr
            [
                'name' => 'Quang An Lake Apartment',
                'district' => 'Tây Hồ',
                'address' => '17 Quảng An, Tây Hồ, Hà Nội',
                'lat' => 21.063456, 'lng' => 105.823478,
                'stars' => 4, 'base_price' => 1200000, 'rating' => 4.8, 'reviews_count' => 723,
                'description' => 'Căn hộ 1 phòng ngủ ban công nhìn thẳng ra Hồ Tây, có bếp đầy đủ — phù hợp ở dài ngày.',
                'amenities' => ['wifi', 'elevator', 'kitchen', 'view', 'lake_view', 'balcony', 'washer', 'workspace'],
                'image' => $img('1582719508461-905c673771fd'),
                'has_vr_tour' => true,
                'rooms' => [
                    ['name' => 'Lake View 1-Bedroom', 'description' => '40m² · 1 phòng ngủ · bếp + ban công Hồ Tây', 'price' => 1200000, 'capacity' => 2],
                    ['name' => 'Lake View Studio', 'description' => '28m² · studio · bếp · ban công', 'price' => 950000, 'capacity' => 2],
                ],
            ],

            // Scenario 14: Hồ Tây + cặp đôi + bồn tắm cạnh cửa sổ + 1.5tr
            [
                'name' => 'Westlake Bathtub Suite',
                'district' => 'Tây Hồ',
                'address' => '34 Tô Ngọc Vân, Tây Hồ, Hà Nội',
                'lat' => 21.068734, 'lng' => 105.821345,
                'stars' => 4, 'base_price' => 1500000, 'rating' => 4.8, 'reviews_count' => 1289,
                'description' => 'Boutique cho cặp đôi với bồn tắm cạnh cửa sổ kính view Hồ Tây — góc chụp ảnh "chill" instafamous.',
                'amenities' => ['wifi', 'elevator', 'breakfast', 'view', 'lake_view', 'bathtub', 'large_window', 'spa', 'romantic'],
                'image' => $img('1571003123894-1f0594d2b5d9'),
                'has_vr_tour' => true,
                'rooms' => [
                    ['name' => 'Romantic Bathtub Suite', 'description' => '38m² · bồn tắm cạnh cửa sổ · giường king', 'price' => 1500000, 'capacity' => 2],
                ],
            ],

            // Scenario 15: Trúc Bạch + thú cưng + <1tr
            [
                'name' => 'Truc Bach Pet-Friendly Homestay',
                'district' => 'Ba Đình',
                'address' => '8 Trấn Vũ, gần Hồ Trúc Bạch, Ba Đình, Hà Nội',
                'lat' => 21.046123, 'lng' => 105.842356,
                'stars' => 3, 'base_price' => 880000, 'rating' => 4.6, 'reviews_count' => 412,
                'description' => 'Homestay gia đình thân thiện với chó/mèo, có khu vườn nhỏ cho thú cưng chạy nhảy.',
                'amenities' => ['wifi', 'kitchen', 'pet_friendly', 'laundry', 'balcony'],
                'image' => $img('1554995207-c18c203602cb'),
                'rooms' => [
                    ['name' => 'Pet-Friendly Double', 'description' => '24m² · cho mang theo 1 thú cưng', 'price' => 880000, 'capacity' => 2],
                    ['name' => 'Pet-Friendly Studio', 'description' => '32m² · bếp + cho thú cưng', 'price' => 980000, 'capacity' => 3],
                ],
            ],

            // Scenario 16: Yên Phụ + ban công + máy lọc kk + 1.1tr
            [
                'name' => 'Yen Phu Allergy-Safe Apartment',
                'district' => 'Tây Hồ',
                'address' => '52 Yên Phụ, Tây Hồ, Hà Nội',
                'lat' => 21.057456, 'lng' => 105.840234,
                'stars' => 3, 'base_price' => 1100000, 'rating' => 4.7, 'reviews_count' => 367,
                'description' => 'Căn hộ phù hợp người dị ứng — máy lọc không khí HEPA mỗi phòng + ban công thoáng.',
                'amenities' => ['wifi', 'elevator', 'kitchen', 'balcony', 'air_purifier', 'view', 'soundproof'],
                'image' => $img('1564501049412-61c2a3083791'),
                'rooms' => [
                    ['name' => 'Allergy-Safe Studio', 'description' => '30m² · máy lọc không khí · ban công', 'price' => 1100000, 'capacity' => 2],
                ],
            ],

            // Scenario 17: Duy Tân + công tác + bàn + wifi + <1tr
            [
                'name' => 'Duy Tan Business Hotel',
                'district' => 'Cầu Giấy',
                'address' => '88 Duy Tân, Cầu Giấy, Hà Nội',
                'lat' => 21.030147, 'lng' => 105.787123,
                'stars' => 3, 'base_price' => 980000, 'rating' => 4.6, 'reviews_count' => 1456,
                'description' => 'KS dành cho khách công tác — bàn làm việc 1.5m, wifi 500Mbps, ổ điện đa quốc gia.',
                'amenities' => ['wifi', 'elevator', 'breakfast', 'parking', 'workspace', 'restaurant', 'concierge'],
                'image' => $img('1542640244-7e672d6cef4e'),
                'rooms' => [
                    ['name' => 'Business Single', 'description' => '22m² · bàn làm việc 1.5m · ổ cắm đa năng', 'price' => 980000, 'capacity' => 1],
                    ['name' => 'Business Double', 'description' => '28m² · 2 bàn làm việc · giường đôi', 'price' => 1180000, 'capacity' => 2],
                ],
            ],

            // Scenario 18: Cầu Giấy + 2 người + gần đường lớn + 600-800k
            [
                'name' => 'Cau Giay Roadside Inn',
                'district' => 'Cầu Giấy',
                'address' => '125 Xuân Thuỷ, sát đường lớn, Cầu Giấy, Hà Nội',
                'lat' => 21.036789, 'lng' => 105.783456,
                'stars' => 3, 'base_price' => 750000, 'rating' => 4.4, 'reviews_count' => 678,
                'description' => 'KS sát đường Xuân Thuỷ, dễ bắt taxi/Grab. Phù hợp khách đi thi hoặc khám xa phải đi nhiều.',
                'amenities' => ['wifi', 'elevator', 'parking', 'breakfast'],
                'image' => $img('1631049307264-da0ec9d70304'),
                'rooms' => [
                    ['name' => 'Standard Double', 'description' => '20m² · giường đôi · view phố lớn', 'price' => 750000, 'capacity' => 2],
                    ['name' => 'Twin Standard', 'description' => '22m² · 2 giường đơn', 'price' => 800000, 'capacity' => 2],
                ],
            ],

            // Scenario 19: Mỹ Đình + đi bộ tới sân + 800k
            [
                'name' => 'My Dinh Stadium Inn',
                'district' => 'Cầu Giấy',
                'address' => '34 Lê Đức Thọ, đi bộ 5 phút tới SVĐ Mỹ Đình, Cầu Giấy, Hà Nội',
                'lat' => 21.018456, 'lng' => 105.766234,
                'stars' => 3, 'base_price' => 800000, 'rating' => 4.5, 'reviews_count' => 832,
                'description' => 'KS đi bộ 5 phút tới SVĐ Mỹ Đình — không lo tắc đường sau concert/trận bóng.',
                'amenities' => ['wifi', 'elevator', 'parking', 'breakfast', 'restaurant'],
                'image' => $img('1582719508461-905c673771fd'),
                'rooms' => [
                    ['name' => 'Stadium View Double', 'description' => '22m² · giường đôi · view sân vận động', 'price' => 800000, 'capacity' => 2],
                    ['name' => 'Stadium Twin', 'description' => '24m² · 2 giường đơn', 'price' => 850000, 'capacity' => 2],
                ],
            ],

            // Scenario 20: Sóc Sơn (gần Nội Bài) + đưa đón + cách âm + 1tr
            [
                'name' => 'Noi Bai Airport Transit Hotel',
                'district' => 'Sóc Sơn',
                'address' => '5 Phú Minh, gần sân bay Nội Bài, Sóc Sơn, Hà Nội',
                'lat' => 21.218234, 'lng' => 105.804567,
                'stars' => 3, 'base_price' => 1000000, 'rating' => 4.5, 'reviews_count' => 1234,
                'description' => 'KS chuyên phục vụ khách bay đêm/sớm — shuttle miễn phí 24h tới sân bay (3 phút), phòng cách âm cao cấp.',
                'amenities' => ['wifi', 'elevator', 'breakfast', 'concierge', 'airport_shuttle', 'soundproof', 'restaurant'],
                'image' => $img('1566073771259-6a8506099945'),
                'rooms' => [
                    ['name' => 'Transit Sleep Pod', 'description' => '18m² · cách âm cấp cao · phù hợp ngủ ngắn', 'price' => 800000, 'capacity' => 1],
                    ['name' => 'Transit Double', 'description' => '24m² · giường đôi · cách âm', 'price' => 1000000, 'capacity' => 2],
                    ['name' => 'Transit Family', 'description' => '32m² · 2 giường đôi', 'price' => 1500000, 'capacity' => 4],
                ],
            ],

            // Bonus: thêm 2 KS nữa lấp khoảng trống
            [
                'name' => 'Noi Bai Premium Airport',
                'district' => 'Sóc Sơn',
                'address' => '12 đường Võ Nguyên Giáp, Sóc Sơn, Hà Nội',
                'lat' => 21.215123, 'lng' => 105.798456,
                'stars' => 4, 'base_price' => 1450000, 'rating' => 4.7, 'reviews_count' => 567,
                'description' => 'KS 4 sao đối diện Nội Bài T2, shuttle 24h, lounge nghỉ ngắn cho khách transit.',
                'amenities' => ['wifi', 'elevator', 'breakfast', 'concierge', 'airport_shuttle', 'soundproof', 'restaurant', 'gym', 'spa'],
                'image' => $img('1564501049412-61c2a3083791'),
                'rooms' => [
                    ['name' => 'Premium Airport King', 'description' => '32m² · giường king · view đường băng', 'price' => 1450000, 'capacity' => 2],
                ],
            ],

            [
                'name' => 'Trúc Bạch Lake Pet Boutique',
                'district' => 'Tây Hồ',
                'address' => '21 Cửa Bắc, Tây Hồ, Hà Nội',
                'lat' => 21.048567, 'lng' => 105.840234,
                'stars' => 3, 'base_price' => 950000, 'rating' => 4.7, 'reviews_count' => 289,
                'description' => 'Boutique nhỏ ven hồ Trúc Bạch, nhận thú cưng và có sân vườn nhỏ.',
                'amenities' => ['wifi', 'kitchen', 'pet_friendly', 'view', 'balcony', 'lake_view'],
                'image' => $img('1554995207-c18c203602cb'),
                'rooms' => [
                    ['name' => 'Pet Lake View Double', 'description' => '26m² · ban công view hồ · cho phép pet', 'price' => 950000, 'capacity' => 2],
                ],
            ],
        ];
    }
}
