<?php

namespace App\Services\Chatbot;

class IntentParser
{
    /** Map district keyword → district name */
    private const DISTRICT_KEYWORDS = [
        'Hoàn Kiếm' => ['hoàn kiếm', 'hoan kiem', 'old quarter'],
        'Tây Hồ' => ['tây hồ', 'tay ho', 'westlake', 'west lake', 'hồ tây', 'ho tay'],
        'Ba Đình' => ['ba đình', 'ba dinh'],
        'Cầu Giấy' => ['cầu giấy', 'cau giay'],
        'Long Biên' => ['long biên', 'long bien'],
        'Hai Bà Trưng' => ['hai bà trưng', 'hai ba trung'],
        'Đống Đa' => ['đống đa', 'dong da'],
        'Hà Đông' => ['hà đông', 'ha dong'],
        'Hoàng Mai' => ['hoàng mai', 'hoang mai'],
        'Sóc Sơn' => ['sóc sơn', 'soc son', 'nội bài', 'noi bai', 'sân bay nội bài', 'san bay noi bai'],
    ];

    /**
     * Map landmark/street/area name → district.
     * `null` district means special case (vd Nội Bài — không thuộc 9 quận seed).
     */
    private const LANDMARK_DISTRICT = [
        // Hoàn Kiếm landmarks
        'phố cổ' => 'Hoàn Kiếm', 'pho co' => 'Hoàn Kiếm',
        'hồ gươm' => 'Hoàn Kiếm', 'ho guom' => 'Hoàn Kiếm', 'hồ hoàn kiếm' => 'Hoàn Kiếm',
        'nhà thờ lớn' => 'Hoàn Kiếm', 'nha tho lon' => 'Hoàn Kiếm',
        'tạ hiện' => 'Hoàn Kiếm', 'ta hien' => 'Hoàn Kiếm',
        'hàng bông' => 'Hoàn Kiếm', 'hang bong' => 'Hoàn Kiếm',
        'hàng bạc' => 'Hoàn Kiếm', 'hang bac' => 'Hoàn Kiếm',
        'hàng mã' => 'Hoàn Kiếm', 'hang ma' => 'Hoàn Kiếm',
        'mã mây' => 'Hoàn Kiếm', 'ma may' => 'Hoàn Kiếm',
        'phố đi bộ' => 'Hoàn Kiếm', 'pho di bo' => 'Hoàn Kiếm',
        'trung tâm' => 'Hoàn Kiếm', 'trung tam' => 'Hoàn Kiếm',
        // Ba Đình landmarks
        'lăng bác' => 'Ba Đình', 'lang bac' => 'Ba Đình',
        'kim mã' => 'Ba Đình', 'kim ma' => 'Ba Đình',
        'liễu giai' => 'Ba Đình', 'lieu giai' => 'Ba Đình',
        'cát linh' => 'Ba Đình', 'cat linh' => 'Ba Đình',
        'trúc bạch' => 'Ba Đình', 'truc bach' => 'Ba Đình',
        // Tây Hồ landmarks
        'quảng an' => 'Tây Hồ', 'quang an' => 'Tây Hồ',
        'yên phụ' => 'Tây Hồ', 'yen phu' => 'Tây Hồ',
        'xuân diệu' => 'Tây Hồ', 'xuan dieu' => 'Tây Hồ',
        'tô ngọc vân' => 'Tây Hồ', 'to ngoc van' => 'Tây Hồ',
        'nghi tàm' => 'Tây Hồ', 'nghi tam' => 'Tây Hồ',
        // Cầu Giấy landmarks
        'mỹ đình' => 'Cầu Giấy', 'my dinh' => 'Cầu Giấy',
        'duy tân' => 'Cầu Giấy', 'duy tan' => 'Cầu Giấy',
        'sân vận động mỹ đình' => 'Cầu Giấy',
        'trần duy hưng' => 'Cầu Giấy', 'tran duy hung' => 'Cầu Giấy',
        'lê đức thọ' => 'Cầu Giấy', 'le duc tho' => 'Cầu Giấy',
        // Đống Đa landmarks
        'bạch mai' => 'Đống Đa', 'bach mai' => 'Đống Đa',
        'bệnh viện bạch mai' => 'Đống Đa', 'benh vien bach mai' => 'Đống Đa',
        'khâm thiên' => 'Đống Đa', 'kham thien' => 'Đống Đa',
        'văn miếu' => 'Đống Đa', 'van mieu' => 'Đống Đa',
        'tôn đức thắng' => 'Đống Đa', 'ton duc thang' => 'Đống Đa',
        // Hai Bà Trưng landmarks
        'phố huế' => 'Hai Bà Trưng', 'pho hue' => 'Hai Bà Trưng',
        'lò đúc' => 'Hai Bà Trưng', 'lo duc' => 'Hai Bà Trưng',
        // Hà Đông
        'tô hiệu' => 'Hà Đông', 'to hieu' => 'Hà Đông',
        'vạn phúc' => 'Hà Đông', 'van phuc' => 'Hà Đông',
        // Hoàng Mai
        'linh đàm' => 'Hoàng Mai', 'linh dam' => 'Hoàng Mai',
        // Long Biên
        'cầu long biên' => 'Long Biên', 'cau long bien' => 'Long Biên',
        'bãi giữa' => 'Long Biên', 'bai giua' => 'Long Biên',
        // Sóc Sơn (sân bay)
        'sân bay nội bài' => 'Sóc Sơn', 'san bay noi bai' => 'Sóc Sơn',
        'nội bài' => 'Sóc Sơn', 'noi bai' => 'Sóc Sơn',
        'sân bay' => 'Sóc Sơn', 'san bay' => 'Sóc Sơn',
    ];

    /**
     * Amenity keywords. Keys are EITHER:
     *   - a key in DB amenities JSON (vd 'pool', 'gym', 'view'),
     *   - OR a "soft preference" — không có trong DB nhưng cần lưu để mention trong response.
     *
     * Soft preferences sẽ KHÔNG được dùng để strict-filter SQL, chỉ dùng để text trong reply.
     */
    private const AMENITY_KEYWORDS = [
        // ===== DB-strict amenities =====
        'pool' => ['bể bơi', 'be boi', 'hồ bơi', 'ho boi', 'pool', 'bơi', 'boi'],
        'gym' => ['gym', 'tập thể', 'tap the', 'thể dục', 'the duc', 'fitness'],
        'spa' => ['spa', 'massage', 'mát xa', 'mat xa', 'xông hơi', 'xong hoi'],
        'view' => ['view đẹp', 'view dep', 'cảnh đẹp', 'canh dep', 'view ra phố', 'view phố', 'view ra hồ', 'view hồ', 'view ho', 'nhìn ra hồ', 'nhin ra ho', 'view nhìn', 'view nhin'],
        'breakfast' => ['ăn sáng', 'an sang', 'bữa sáng', 'bua sang', 'breakfast', 'buffet'],
        'parking' => ['đỗ xe', 'do xe', 'parking', 'gửi xe', 'gui xe', 'gửi ô tô', 'gui o to', 'chỗ để xe', 'cho de xe'],
        'wifi' => ['wifi', 'internet', 'mạng mạnh', 'mang manh'],
        'restaurant' => ['nhà hàng', 'nha hang', 'restaurant'],
        'bar' => ['quầy bar', 'quay bar', 'bar rượu'],
        'elevator' => ['thang máy', 'thang may', 'thẻ từ thang máy', 'the tu thang may'],
        'concierge' => ['lễ tân 24', 'le tan 24', 'lễ tân 24/24', 'lễ tân 24/7', 'concierge', '24/7', '24/24'],
        'laundry' => ['máy giặt', 'may giat', 'giặt đồ', 'giat do', 'phơi đồ', 'phoi do', 'laundry'],

        // ===== Soft preferences (chưa có trong DB seed — dùng để chú thích trong reply) =====
        'kitchen' => ['bếp', 'bep', 'nấu ăn', 'nau an', 'nấu cháo', 'nau chao', 'kitchen', 'tự nấu', 'tu nau', 'bếp chung'],
        'balcony' => ['ban công', 'ban cong', 'balcony', 'sân thượng', 'san thuong'],
        'smart_tv' => ['smart tv', 'netflix', 'tivi thông minh', 'tivi smart', 'xem phim'],
        'soundproof' => ['cách âm', 'cach am', 'yên tĩnh', 'yen tinh', 'không ồn', 'khong on', 'phòng ngủ tốt'],
        'pet_friendly' => ['thú cưng', 'thu cung', 'mang chó', 'mang cho', 'mang mèo', 'mang meo', 'pet', 'cho mang theo'],
        'bathtub' => ['bồn tắm', 'bon tam', 'bathtub', 'tắm bồn'],
        'large_window' => ['cửa sổ lớn', 'cua so lon', 'cửa sổ to', 'cua so to', 'thoáng', 'thoang', 'không bí bách', 'khong bi bach'],
        'workspace' => ['bàn làm việc', 'ban lam viec', 'làm việc', 'lam viec', 'công tác', 'cong tac', 'business'],
        'airport_shuttle' => ['đưa đón sân bay', 'dua don san bay', 'shuttle', 'đón sân bay', 'don san bay', 'xe đưa đón'],
        'lake_view' => ['view hồ', 'view ho', 'nhìn ra hồ', 'nhin ra ho', 'thẳng ra hồ', 'thang ra ho'],
        'street_view' => ['view phố', 'view pho', 'view ra phố', 'view ra pho', 'ra phố', 'ra pho'],
        'security_24_7' => ['an ninh', 'an toàn', 'an toan', 'cho nữ', 'cho nu', 'phụ nữ một mình', 'phu nu mot minh', 'nữ đi một mình'],
        'air_purifier' => ['máy lọc không khí', 'may loc khong khi', 'air purifier', 'dị ứng', 'di ung', 'viêm mũi'],
        'open_bathroom' => ['phòng tắm kính', 'phong tam kinh', 'open bathroom', 'kính trong suốt', 'kinh trong suot'],
        'large_room' => ['phòng rộng', 'phong rong', 'phòng to', 'phong to', 'spacious'],
        'family_room' => ['phòng gia đình', 'phong gia dinh', '2 giường đôi', '2 giuong doi', 'family room'],
    ];

    /** Map các star qua keyword */
    private const STAR_KEYWORDS = [
        5 => ['5 sao', '5sao', 'năm sao', 'nam sao', '5★', 'cao cấp', 'cao cap', 'luxury'],
        4 => ['4 sao', '4sao', 'bốn sao', 'bon sao', '4★', 'như khách sạn 4 sao', 'như ks 4 sao'],
        3 => ['3 sao', '3sao', 'ba sao', '3★'],
    ];

    /** Scenarios — mục đích chuyến đi, ảnh hưởng tone reply */
    private const SCENARIOS = [
        'medical' => ['bệnh viện', 'benh vien', 'đi khám', 'di kham', 'khám bệnh', 'kham benh', 'điều trị', 'dieu tri', 'người nhà ốm', 'nguoi nha om'],
        'business' => ['công tác', 'cong tac', 'đi làm', 'di lam', 'business trip', 'họp hành', 'hop hanh'],
        'exam' => ['đi thi', 'di thi', 'kỳ thi', 'ky thi', 'thi đại học', 'thi dai hoc'],
        'concert' => ['concert', 'sự kiện', 'su kien', 'show', 'biểu diễn', 'bieu dien'],
        'family_elderly' => ['người già', 'nguoi gia', 'cụ', 'ông bà', 'ong ba', 'cao tuổi', 'cao tuoi'],
        'late_flight' => ['chuyến bay đêm', 'chuyen bay dem', 'bay khuya', 'bay đêm', 'bay dem', 'sân bay', 'san bay', 'ngủ bù', 'ngu bu'],
        'budget_only' => ['giá rẻ', 'gia re', 'tiết kiệm', 'tiet kiem', 'rẻ thôi', 're thoi', 'chỉ cần ngủ', 'chi can ngu'],
        'romantic' => ['cặp đôi', 'cap doi', 'romantic', 'lãng mạn', 'lang man', 'kỉ niệm', 'ki niem', 'chill'],
    ];

    public function parse(string $message): array
    {
        $lower = mb_strtolower($message);

        $districtMatches = $this->extractMatches($lower, self::DISTRICT_KEYWORDS);
        $landmarkResult = $this->extractLandmark($lower);
        $amenitiesAll = $this->extractMatches($lower, self::AMENITY_KEYWORDS);

        // Tất cả keys hiện đã có trong seed data → strict-filter được hết
        $dbAmenityKeys = [
            'pool', 'gym', 'spa', 'view', 'breakfast', 'parking', 'wifi', 'restaurant', 'bar',
            'elevator', 'concierge', 'laundry',
            'kitchen', 'balcony', 'smart_tv', 'soundproof', 'pet_friendly', 'bathtub',
            'large_window', 'workspace', 'airport_shuttle', 'lake_view', 'street_view',
            'security_24_7', 'air_purifier', 'open_bathroom', 'large_room', 'family_room',
            'washer',
        ];
        $strictAmenities = array_values(array_intersect($amenitiesAll, $dbAmenityKeys));
        $softFeatures = array_values(array_diff($amenitiesAll, $dbAmenityKeys));

        // Merge district từ keyword + từ landmark
        $districts = array_values(array_unique(array_filter(
            array_merge($districtMatches, array_filter([$landmarkResult['district']]))
        )));

        return [
            'districts' => $districts,
            'landmark' => $landmarkResult['name'],
            'is_special_landmark' => $landmarkResult['is_special'],
            'amenities' => $strictAmenities,
            'soft_features' => $softFeatures,
            'scenarios' => $this->extractScenarios($lower),
            'price' => $this->extractPrice($lower),
            'guests' => $this->extractGuests($lower),
            'stars' => $this->extractStars($lower),
            'wants_recommendation' => $this->isAskingForRecommendation($lower),
            'is_greeting' => $this->isGreeting($lower),
            'is_thanks' => $this->isThanks($lower),
        ];
    }

    private function extractMatches(string $text, array $keywordMap): array
    {
        $found = [];
        foreach ($keywordMap as $key => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($text, $kw)) {
                    $found[] = $key;
                    break;
                }
            }
        }
        return array_values(array_unique($found));
    }

    /**
     * @return array{name: ?string, district: ?string, is_special: bool}
     */
    private function extractLandmark(string $text): array
    {
        // Sort theo độ dài giảm dần để match cụm dài trước (vd "bệnh viện bạch mai" trước "bạch mai")
        $entries = self::LANDMARK_DISTRICT;
        uksort($entries, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        foreach ($entries as $landmark => $district) {
            if (str_contains($text, $landmark)) {
                return [
                    'name' => $landmark,
                    'district' => $district,
                    'is_special' => $district === null,
                ];
            }
        }

        return ['name' => null, 'district' => null, 'is_special' => false];
    }

    private function extractScenarios(string $text): array
    {
        return $this->extractMatches($text, self::SCENARIOS);
    }

    private function extractPrice(string $text): ?array
    {
        $max = null;
        $min = null;

        // Range với unit ở cả 2 số: "600k - 800k", "1tr - 2tr", "1 triệu đến 2 triệu"
        if (preg_match('/([\d.,]+)\s*(tr|triệu|trieu|k|nghìn|nghin)\s*(?:-|–|—|đến|den|tới|toi|to)\s*([\d.,]+)\s*(tr|triệu|trieu|k|nghìn|nghin)/u', $text, $m)) {
            $min = $this->normalizePrice($m[1], $m[2]);
            $max = $this->normalizePrice($m[3], $m[4]);
        }
        // Range với unit chỉ ở số sau: "600 - 800k", "1 - 2 triệu"
        elseif (preg_match('/([\d.,]+)\s*(?:-|–|—|đến|den|tới|toi)\s*([\d.,]+)\s*(tr|triệu|trieu|k|nghìn|nghin)/u', $text, $m)) {
            $min = $this->normalizePrice($m[1], $m[3]);
            $max = $this->normalizePrice($m[2], $m[3]);
        }
        // Dưới X / tầm X / khoảng X
        elseif (preg_match('/(?:dưới|duoi|under|<|less than|tầm|tam|khoảng|khoang|loanh quanh)\s*([\d.,]+)\s*(tr|triệu|trieu|k|nghìn|nghin)/u', $text, $m)) {
            $max = $this->normalizePrice($m[1], $m[2]);
        }
        // Trên X
        elseif (preg_match('/(?:trên|tren|over|>|more than)\s*([\d.,]+)\s*(tr|triệu|trieu|k|nghìn|nghin)/u', $text, $m)) {
            $min = $this->normalizePrice($m[1], $m[2]);
        }
        // Đơn lẻ "X tr/triệu/k": treat as max budget
        elseif (preg_match('/([\d.,]+)\s*(tr|triệu|trieu|k|nghìn|nghin)/u', $text, $m)) {
            $max = $this->normalizePrice($m[1], $m[2]);
        }

        if ($min === null && $max === null) {
            return null;
        }

        return ['min' => $min ?? 0, 'max' => $max ?? PHP_INT_MAX];
    }

    private function normalizePrice(string $num, string $unit): int
    {
        $unit = mb_strtolower($unit);

        // Decimal Vietnamese: "1,2" hoặc "1.5" (1-2 chữ số sau separator) → 1.2 / 1.5
        if (preg_match('/^(\d+)[,.](\d{1,2})$/', $num, $m)) {
            $val = (float) ($m[1] . '.' . $m[2]);
        } else {
            // Còn lại bỏ thousand separators (1.200.000 → 1200000)
            $val = (float) str_replace([',', '.'], '', $num);
        }

        if (in_array($unit, ['tr', 'triệu', 'trieu'], true)) {
            return (int) round($val * 1_000_000);
        }
        if (in_array($unit, ['k', 'nghìn', 'nghin'], true)) {
            return (int) round($val * 1_000);
        }

        return (int) $val;
    }

    private function extractGuests(string $text): ?int
    {
        if (preg_match('/(\d+)\s*(?:người|nguoi|khách|khach|guests?|people|persons?|pax)/u', $text, $m)) {
            return (int) $m[1];
        }
        if (preg_match('/(\d+)\s*người lớn/u', $text, $m)) {
            return (int) $m[1];
        }
        if (str_contains($text, 'cặp đôi') || str_contains($text, 'cap doi') || str_contains($text, 'couple')) {
            return 2;
        }
        if (preg_match('/gia đình\s*(\d+)/u', $text, $m)) {
            return (int) $m[1];
        }
        if (str_contains($text, 'gia đình') || str_contains($text, 'gia dinh') || str_contains($text, 'family')) {
            return 4;
        }
        return null;
    }

    private function extractStars(string $text): ?int
    {
        foreach (self::STAR_KEYWORDS as $star => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($text, $kw)) {
                    return $star;
                }
            }
        }
        return null;
    }

    private function isAskingForRecommendation(string $text): bool
    {
        $patterns = ['gợi ý', 'goi y', 'tìm', 'tim', 'đề xuất', 'de xuat', 'cần', 'can', 'muốn', 'muon', 'đặt', 'dat', 'thuê', 'thue'];
        foreach ($patterns as $p) {
            if (str_contains($text, $p)) {
                return true;
            }
        }
        return false;
    }

    private function isGreeting(string $text): bool
    {
        $greetings = ['xin chào', 'xin chao', 'chào smarty', 'chao smarty', 'hello', 'hey'];
        foreach ($greetings as $g) {
            if (str_starts_with($text, $g) || str_contains($text, ' ' . $g)) {
                return true;
            }
        }
        return mb_strlen($text) <= 5 && in_array(trim($text), ['hi', 'hello', 'chào', 'chao']);
    }

    private function isThanks(string $text): bool
    {
        return str_contains($text, 'cảm ơn') || str_contains($text, 'cam on')
            || str_contains($text, 'thanks') || str_contains($text, 'thank you') || str_contains($text, 'tks');
    }
}
