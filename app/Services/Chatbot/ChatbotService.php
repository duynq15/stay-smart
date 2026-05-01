<?php

namespace App\Services\Chatbot;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\Hotel;
use App\Models\Place;
use Illuminate\Support\Facades\DB;

class ChatbotService
{
    /**
     * Soft feature label + advice cụ thể (thay vì lặp "vui lòng nhắn KS")
     * 'label' để liệt kê, 'advice' là gợi ý khả thi cho user
     */
    private const SOFT_FEATURE_INFO = [
        'kitchen' => ['label' => 'có bếp nấu ăn', 'advice' => 'phần lớn khách sạn ở Hà Nội không có bếp riêng — bạn thử dạng homestay hoặc căn hộ dịch vụ (serviced apartment) sẽ đúng nhu cầu hơn'],
        'balcony' => ['label' => 'có ban công', 'advice' => 'bạn yêu cầu phòng tầng cao có ban công khi check-in nhé, các phòng Deluxe thường có'],
        'smart_tv' => ['label' => 'Smart TV / Netflix', 'advice' => 'hầu hết KS 4-5 sao đều có Smart TV với Netflix, bạn check trước khi đặt'],
        'soundproof' => ['label' => 'phòng cách âm', 'advice' => 'nên chọn phòng tầng cao và xa mặt đường — KS sẽ ưu tiên xếp phòng yên tĩnh nếu bạn ghi chú'],
        'pet_friendly' => ['label' => 'cho phép mang thú cưng', 'advice' => 'tính năng này hiếm ở khách sạn — bạn nên tìm homestay riêng hoặc gọi KS xác nhận trước'],
        'bathtub' => ['label' => 'có bồn tắm', 'advice' => 'bồn tắm thường chỉ có ở phòng Suite/Premium — bạn chọn các phòng cao cấp khi đặt'],
        'large_window' => ['label' => 'cửa sổ lớn / phòng thoáng', 'advice' => 'bạn nên đặt phòng Deluxe trở lên và yêu cầu phòng có cửa sổ khi check-in'],
        'workspace' => ['label' => 'bàn làm việc', 'advice' => 'KS 4-5 sao đa phần đều có bàn làm việc rộng, bạn yên tâm'],
        'airport_shuttle' => ['label' => 'đưa đón sân bay', 'advice' => 'bạn liên hệ KS đặt dịch vụ đưa đón hoặc dùng Grab/taxi cho nhanh'],
        'lake_view' => ['label' => 'view nhìn ra hồ', 'advice' => 'bạn ưu tiên KS có chữ "Lake View" trong tên phòng và chọn tầng cao'],
        'street_view' => ['label' => 'view ra phố', 'advice' => 'bạn yêu cầu phòng hướng đường khi đặt'],
        'security' => ['label' => 'an ninh tốt cho phụ nữ một mình', 'advice' => 'các KS 4-5 sao có thẻ từ thang máy + lễ tân 24/7 sẽ phù hợp nhất'],
        'air_purifier' => ['label' => 'máy lọc không khí', 'advice' => 'bạn liên hệ trước với KS — nhiều nơi có thể bố trí khi yêu cầu'],
        'open_bathroom' => ['label' => 'phòng tắm kính', 'advice' => 'thiết kế này thường gặp ở các KS boutique/4-5 sao mới'],
        'large_room' => ['label' => 'phòng rộng', 'advice' => 'bạn nên đặt từ phòng Deluxe trở lên (>=30m²)'],
        'family_room' => ['label' => 'phòng gia đình / 2 giường đôi', 'advice' => 'bạn tìm phòng "Family Suite" hoặc đặt 2 phòng Twin'],
    ];

    /** Tiện nghi → nhãn tiếng Việt tự nhiên (dùng trong câu) */
    private const AMENITY_VI_LABELS = [
        'pool' => 'bể bơi',
        'gym' => 'phòng gym',
        'spa' => 'spa',
        'view' => 'view đẹp',
        'breakfast' => 'bữa sáng',
        'parking' => 'chỗ đỗ xe',
        'wifi' => 'wifi mạnh',
        'restaurant' => 'nhà hàng',
        'bar' => 'quầy bar',
        'elevator' => 'thang máy',
        'concierge' => 'lễ tân 24/7',
        'laundry' => 'dịch vụ giặt ủi',
        // Mới
        'kitchen' => 'bếp nấu ăn',
        'balcony' => 'ban công',
        'smart_tv' => 'Smart TV',
        'soundproof' => 'phòng cách âm',
        'pet_friendly' => 'cho thú cưng',
        'bathtub' => 'bồn tắm',
        'large_window' => 'cửa sổ lớn',
        'workspace' => 'bàn làm việc',
        'airport_shuttle' => 'xe đưa đón sân bay',
        'lake_view' => 'view ra hồ',
        'street_view' => 'view ra phố',
        'security_24_7' => 'an ninh 24/7',
        'air_purifier' => 'máy lọc không khí',
        'open_bathroom' => 'phòng tắm kính',
        'large_room' => 'phòng rộng',
        'family_room' => 'phòng gia đình',
        'washer' => 'máy giặt',
    ];

    /** Câu mở đầu thân thiện theo scenario — tự đủ nghĩa, không cụt */
    private const SCENARIO_INTRO = [
        'medical' => 'Mong người nhà bạn mau khoẻ. Mình tìm khách sạn thuận tiện qua lại bệnh viện cho bạn nhé',
        'business' => 'Đi công tác thì mình ưu tiên khách sạn yên tĩnh, có không gian làm việc',
        'exam' => 'Cho kỳ thi quan trọng, mình chọn chỗ ngủ yên gần điểm thi',
        'concert' => 'Đi xem concert mình ưu tiên khách sạn gần địa điểm để tránh tắc đường sau show',
        'family_elderly' => 'Đi cùng người lớn tuổi nên mình chọn khách sạn có thang máy và lễ tân hỗ trợ tận nơi',
        'late_flight' => 'Bay đêm rất mệt, bạn cần chỗ nghỉ yên tĩnh để hồi sức',
        'budget_only' => 'Mình tìm vài lựa chọn sạch sẽ và giá tốt cho bạn',
        'romantic' => 'Cho chuyến đi đôi đáng nhớ, mình chọn khách sạn riêng tư view đẹp',
    ];

    public function __construct(
        private IntentParser $parser,
        private LlmClient $llm,
    ) {}

    public function startSession(?int $userId): ChatSession
    {
        return ChatSession::create([
            'user_id' => $userId,
            'started_at' => now(),
            'message_count' => 0,
        ]);
    }

    public function reply(ChatSession $session, string $userMessage): array
    {
        $this->saveMessage($session, 'user', $userMessage);

        $intent = $this->parser->parse($userMessage);
        $response = $this->buildResponse($intent, $userMessage, $session);

        $this->saveMessage($session, 'bot', $response['text']);

        return $response;
    }

    public function suggestPlaces(ChatSession $session, string $district, string $hotelName): array
    {
        $places = Place::query()
            ->where('district', $district)
            ->where('is_active', true)
            ->orderByDesc('rating')
            ->limit(6)
            ->get(['id', 'name', 'type', 'address', 'rating', 'avg_price', 'image_url']);

        if ($places->isEmpty()) {
            $places = Place::query()
                ->where('is_active', true)
                ->orderByDesc('rating')
                ->limit(6)
                ->get(['id', 'name', 'type', 'address', 'rating', 'avg_price', 'image_url']);
        }

        $text = "Đây là vài địa điểm hay quanh {$hotelName} mà tôi gợi ý cho bạn:";
        $this->saveMessage($session, 'bot', $text);

        return [
            'text' => $text,
            'places' => $places->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'type' => $p->type,
                'address' => $p->address,
                'rating' => $p->rating,
                'avg_price' => $p->avg_price,
                'image_url' => $p->image_url,
                'tag' => $this->placeTypeTag($p->type),
            ])->values(),
        ];
    }

    public function suggestDeclined(ChatSession $session, string $hotelName): array
    {
        $text = "Vâng, tôi đã hiểu. Chúc quý khách có một trải nghiệm tuyệt vời tại {$hotelName} 🌿. Nếu cần đặt thêm chỗ hay cần hỗ trợ gì, cứ nhắn cho Smarty nhé!";
        $this->saveMessage($session, 'bot', $text);

        return ['text' => $text];
    }

    private function buildResponse(array $intent, string $userMessage, ChatSession $session): array
    {
        if ($intent['is_greeting']) {
            return [
                'text' => "Chào bạn! Tôi là Smarty 👋 Bạn đang tìm khách sạn ở khu nào, ngân sách bao nhiêu, cần tiện nghi gì? Cứ mô tả tự nhiên nhé!",
                'quick_replies' => [
                    'Khách sạn 5 sao Hoàn Kiếm',
                    'View Hồ Tây dưới 3tr',
                    'Có bể bơi và spa',
                    '2 người, 1tr/đêm',
                ],
            ];
        }

        if ($intent['is_thanks']) {
            return ['text' => "Rất vui được hỗ trợ bạn! 🌿 Nếu cần thêm gợi ý, cứ nhắn nhé."];
        }

        $hasFilter = ! empty($intent['districts'])
            || ! empty($intent['amenities'])
            || ! empty($intent['soft_features'])
            || $intent['price'] !== null
            || $intent['stars'] !== null
            || $intent['guests'] !== null;

        if ($hasFilter) {
            // Cascading relax: 0=strict, 1=bỏ amenity, 2=bỏ guests, 3=nới giá 1.5x, 4=bỏ district
            $relaxLevel = 0;
            $hotels = collect();

            for ($lvl = 0; $lvl <= 4; $lvl++) {
                $hotels = $this->queryHotels($intent, $lvl);
                if ($hotels->isNotEmpty()) {
                    $relaxLevel = $lvl;
                    break;
                }
            }

            if ($hotels->isEmpty()) {
                return [
                    'text' => "Xin lỗi, hiện tại STAY-SMART chưa có khách sạn phù hợp với yêu cầu của bạn. Bạn có thể thử mô tả khác hoặc liên hệ tổng đài 1900 6868 để được tư vấn trực tiếp.",
                    'quick_replies' => ['Khách sạn 5 sao Hà Nội', 'Khu Hoàn Kiếm', 'Dưới 1 triệu'],
                ];
            }

            return [
                'text' => $this->buildSummary($intent, $hotels->count(), $relaxLevel),
                'hotels' => $hotels->map(fn ($h) => $this->formatHotelCard($h))->values(),
            ];
        }

        // Free-form chat: thử LLM nếu được bật
        if ($this->llm->isEnabled()) {
            $aiText = $this->llm->generate($userMessage, $session);
            if ($aiText !== null && trim($aiText) !== '') {
                return ['text' => trim($aiText)];
            }
        }

        return [
            'text' => "Bạn cho mình biết thêm: muốn ở khu nào (Hoàn Kiếm, Tây Hồ, Ba Đình...), ngân sách bao nhiêu, mấy người, cần tiện nghi gì (bể bơi, gym, view...) nhé?",
            'quick_replies' => [
                'Hoàn Kiếm dưới 2tr',
                'Tây Hồ view hồ',
                'Có bể bơi',
                '5 sao Ba Đình',
            ],
        ];
    }

    /**
     * Query KS theo cascading relax level.
     *
     * Level 0 = strict (district + amenities + price + stars + guests)
     * Level 1 = bỏ amenity filter
     * Level 2 = bỏ guest capacity
     * Level 3 = nới giá 1.5x
     * Level 4 = bỏ district (chỉ còn stars + price nới)
     */
    private function queryHotels(array $intent, int $relaxLevel = 0)
    {
        $q = Hotel::query()
            ->where('is_active', true)
            ->with('primaryImage');

        // District: giữ tới level 3, bỏ ở level 4
        if (! empty($intent['districts']) && $relaxLevel < 4) {
            $q->whereIn('district', $intent['districts']);
        }

        // Amenities: chỉ filter ở level 0
        if ($relaxLevel === 0 && ! empty($intent['amenities'])) {
            foreach ($intent['amenities'] as $a) {
                $q->whereJsonContains('amenities', $a);
            }
        }

        // Price: nới 1.5x từ level 3 trở lên
        if ($intent['price']) {
            $maxPrice = $intent['price']['max'];
            if ($relaxLevel >= 3 && $maxPrice < PHP_INT_MAX) {
                $maxPrice = (int) round($maxPrice * 1.5);
            }
            $q->whereBetween('base_price', [$intent['price']['min'], $maxPrice]);
        }

        if ($intent['stars']) {
            $q->where('stars', $intent['stars']);
        }

        // Guests capacity: filter tới level 1, bỏ từ level 2
        if ($relaxLevel < 2 && $intent['guests'] !== null && $intent['guests'] > 0) {
            $q->whereHas('rooms', function ($r) use ($intent) {
                $r->where('is_active', true)
                    ->where('capacity', '>=', $intent['guests']);
            });
        }

        return $q->orderByDesc('rating')->orderByDesc('reviews_count')->limit(4)->get();
    }

    private function buildSummary(array $intent, int $count, int $relaxLevel = 0): string
    {
        $lines = [];

        // 1. Câu mở đầu thân thiện theo scenario
        if (! empty($intent['scenarios'])) {
            $intro = self::SCENARIO_INTRO[$intent['scenarios'][0]] ?? null;
            if ($intro) {
                $lines[] = $intro . '.';
            }
        }

        if ($relaxLevel === 0) {
            // Match đúng — câu khẳng định ngắn gọn
            $criteria = $this->describeCriteriaNatural($intent, false);
            $lines[] = "Mình tìm thấy {$count} khách sạn {$criteria}:";
        } else {
            // Phải nới — thừa nhận chưa tìm được rồi mới đưa lựa chọn
            $original = $this->describeCriteriaNatural($intent, false);
            $lines[] = "Tiếc là hiện chưa có khách sạn nào khớp đủ yêu cầu của bạn — {$original}.";

            $relaxNotes = $this->buildRelaxNotes($intent, $relaxLevel);
            if (! empty($relaxNotes)) {
                $lines[] = 'Mình thử ' . implode(', ', $relaxNotes) . ' để bạn có nhiều lựa chọn hơn.';
            }

            $relaxed = $this->describeCriteriaNatural($intent, true, $relaxLevel);
            $lines[] = "Đây là {$count} gợi ý gần nhất {$relaxed}:";
        }

        $body = implode("\n\n", $lines);

        // 2. Footer cho soft features (advice cụ thể cho từng feature)
        if (! empty($intent['soft_features'])) {
            $body .= "\n\n" . $this->softFeaturesFooter($intent['soft_features']);
        }

        return $body;
    }

    /**
     * Mô tả tiêu chí dạng cụm từ tự nhiên (đặt sau "khách sạn" trong câu).
     * Vd: "5 sao có bể bơi và spa ở Tây Hồ"
     *     "dưới 1 triệu cho 4 người ở khu phố cổ (Hoàn Kiếm)"
     */
    private function describeCriteriaNatural(array $intent, bool $useRelaxed, int $relaxLevel = 0): string
    {
        $segments = [];

        if ($intent['stars']) {
            $segments[] = "{$intent['stars']} sao";
        }

        if ($intent['price'] && $intent['price']['max'] < PHP_INT_MAX) {
            $max = $intent['price']['max'];
            if ($useRelaxed && $relaxLevel >= 3) {
                $max = (int) round($max * 1.5);
            }
            $segments[] = 'dưới ' . $this->formatPrice($max);
        }

        if ($intent['guests'] && (! $useRelaxed || $relaxLevel < 2)) {
            $segments[] = "cho {$intent['guests']} người";
        }

        if (! empty($intent['amenities']) && (! $useRelaxed || $relaxLevel === 0)) {
            $names = array_map(fn ($a) => self::AMENITY_VI_LABELS[$a] ?? $a, $intent['amenities']);
            $segments[] = 'có ' . $this->joinVi($names);
        }

        // Vị trí ở cuối, nối bằng "ở"
        if (! empty($intent['districts']) && (! $useRelaxed || $relaxLevel < 4)) {
            $district = implode(', ', $intent['districts']);
            $segments[] = $intent['landmark']
                ? "ở khu {$this->displayLandmark($intent['landmark'])} ({$district})"
                : "ở {$district}";
        } elseif ($useRelaxed && $relaxLevel >= 4) {
            $segments[] = 'trên toàn Hà Nội';
        }

        if (empty($segments)) {
            return 'phù hợp với bạn';
        }

        // Space-join, không dùng "và" giữa các segment khác loại
        return implode(' ', $segments);
    }

    /** Nối list bằng dấu phẩy + "và" cuối cùng (vd "A, B và C") */
    private function joinVi(array $items): string
    {
        $items = array_values(array_filter($items, fn ($i) => $i !== '' && $i !== null));
        if (count($items) === 0) return '';
        if (count($items) === 1) return $items[0];
        if (count($items) === 2) return $items[0] . ' và ' . $items[1];

        $last = array_pop($items);
        return implode(', ', $items) . ' và ' . $last;
    }

    private function formatPrice(int $amount): string
    {
        if ($amount >= 1_000_000 && $amount % 100_000 === 0) {
            $tr = $amount / 1_000_000;
            // Decimal kiểu Việt: dùng dấu phẩy thay vì dấu chấm (1,5 triệu)
            $formatted = rtrim(rtrim(number_format($tr, 1, ',', ''), '0'), ',');
            return $formatted . ' triệu';
        }
        if ($amount >= 1_000 && $amount % 1_000 === 0) {
            return ($amount / 1_000) . 'k';
        }
        return number_format($amount, 0, ',', '.') . 'đ';
    }

    /** Title-case landmark cho hiển thị ("bệnh viện bạch mai" → "Bệnh viện Bạch Mai") */
    private function displayLandmark(string $landmark): string
    {
        // Các từ giữ thường (giới từ, hậu tố ngắn)
        $lowercase = ['ở', 'và', 'của', 'tại'];
        $words = preg_split('/\s+/', trim($landmark)) ?: [];
        $result = [];
        foreach ($words as $i => $word) {
            if ($i > 0 && in_array($word, $lowercase, true)) {
                $result[] = $word;
            } else {
                $result[] = mb_convert_case($word, MB_CASE_TITLE, 'UTF-8');
            }
        }
        return implode(' ', $result);
    }

    /** Footer cho soft features — kèm advice cụ thể */
    private function softFeaturesFooter(array $features): string
    {
        $infos = [];
        foreach ($features as $f) {
            if (isset(self::SOFT_FEATURE_INFO[$f])) {
                $infos[] = self::SOFT_FEATURE_INFO[$f];
            }
        }

        if (empty($infos)) {
            return '';
        }

        if (count($infos) === 1) {
            return "💡 Về **{$infos[0]['label']}**: {$infos[0]['advice']}.";
        }

        $lines = ["💡 Vài lưu ý cho yêu cầu của bạn:"];
        foreach ($infos as $info) {
            $lines[] = "• **{$info['label']}**: {$info['advice']}.";
        }

        return implode("\n", $lines);
    }

    /** Mô tả những gì đã nới, theo level — dạng câu Việt tự nhiên */
    private function buildRelaxNotes(array $intent, int $relaxLevel): array
    {
        $notes = [];

        if ($relaxLevel >= 1 && ! empty($intent['amenities'])) {
            $names = array_map(fn ($a) => self::AMENITY_VI_LABELS[$a] ?? $a, $intent['amenities']);
            $notes[] = 'bỏ qua yêu cầu ' . $this->joinVi($names);
        }

        if ($relaxLevel >= 2 && $intent['guests']) {
            $notes[] = "không lọc riêng phòng cho {$intent['guests']} khách (KS thường chỉ có phòng đôi — bạn có thể đặt 2 phòng nếu cần)";
        }

        if ($relaxLevel >= 3 && $intent['price']) {
            $original = $this->formatPrice($intent['price']['max']);
            $relaxed = $this->formatPrice((int) round($intent['price']['max'] * 1.5));
            $notes[] = "nâng ngân sách từ {$original} lên {$relaxed}";
        }

        if ($relaxLevel >= 4 && ! empty($intent['districts'])) {
            $original = implode(', ', $intent['districts']);
            $notes[] = "mở rộng tìm kiếm từ {$original} ra toàn Hà Nội";
        }

        return $notes;
    }

    private function formatHotelCard(Hotel $hotel): array
    {
        return [
            'id' => $hotel->id,
            'name' => $hotel->name,
            'slug' => $hotel->slug,
            'district' => $hotel->district,
            'address' => $hotel->address,
            'price' => $hotel->base_price,
            'rating' => $hotel->rating,
            'reviews_count' => $hotel->reviews_count,
            'image' => $hotel->primaryImage?->url ?? 'https://placehold.co/200x200/14724f/fbf8f1?text=Hotel',
        ];
    }

    private function placeTypeTag(string $type): string
    {
        return match ($type) {
            'restaurant' => 'Nhà hàng',
            'cafe' => 'Quán cafe',
            'attraction' => 'Tham quan',
            'shopping' => 'Mua sắm',
            'bar' => 'Quán bar',
            'spa' => 'Spa',
            default => 'Địa điểm',
        };
    }

    private function saveMessage(ChatSession $session, string $sender, string $content): void
    {
        DB::transaction(function () use ($session, $sender, $content) {
            ChatMessage::create([
                'session_id' => $session->id,
                'sender' => $sender,
                'content' => $content,
            ]);
            $session->increment('message_count');
        });
    }
}
