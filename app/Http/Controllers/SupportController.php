<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SupportController extends Controller
{
    public function index(): Response
    {
        $faqs = [
            [
                'q' => 'Làm sao để đặt phòng?',
                'a' => 'Bạn có thể trò chuyện với Smarty để được tư vấn, hoặc tự tìm khách sạn trong tab Khách sạn. Sau đó chọn phòng → điền thông tin → thanh toán.',
            ],
            [
                'q' => 'STAY-SMART có nhận thanh toán bằng MoMo / VNPay không?',
                'a' => 'Có. Hệ thống hỗ trợ Thẻ tín dụng, VNPay, MoMo, Chuyển khoản và Trả tại khách sạn. (Demo · không trừ tiền thật)',
            ],
            [
                'q' => 'Tôi có thể hủy đặt phòng không?',
                'a' => 'Tùy chính sách của từng khách sạn. Đa số khách sạn cho phép hủy miễn phí trước 48h check-in. Liên hệ Smarty hoặc trực tiếp khách sạn để biết chi tiết.',
            ],
            [
                'q' => 'Smarty AI hoạt động thế nào?',
                'a' => 'Smarty là trợ lý AI hiểu tiếng Việt. Bạn có thể mô tả tự nhiên: "Tìm khách sạn 5 sao Hoàn Kiếm có view, dưới 3 triệu". Smarty sẽ phân tích và đề xuất KS phù hợp.',
            ],
            [
                'q' => 'Khu vực phục vụ?',
                'a' => 'Hiện tại STAY-SMART chỉ phục vụ khách sạn tại Hà Nội (Hoàn Kiếm, Tây Hồ, Ba Đình, Cầu Giấy, Hai Bà Trưng, Đống Đa, Long Biên, Hà Đông, Hoàng Mai...).',
            ],
            [
                'q' => 'Có VR Tour 360° cho mọi khách sạn không?',
                'a' => 'Hầu hết khách sạn 4-5 sao đều có VR Tour. Bạn sẽ thấy badge "VR Tour" trên ảnh đại diện. Nhấn nút "Xem VR Tour" trong trang chi tiết để khám phá phòng trước khi đặt.',
            ],
            [
                'q' => 'Tôi quên mật khẩu, làm sao đăng nhập lại?',
                'a' => 'Liên hệ admin@staysmart.vn để được hỗ trợ reset mật khẩu. (Demo · chưa có flow tự động)',
            ],
            [
                'q' => 'Có thể đặt cho người khác không?',
                'a' => 'Hoàn toàn được. Khi đặt phòng bạn có thể điền thông tin của người sẽ ở trong mục "Thông tin khách". Email xác nhận sẽ gửi tới địa chỉ bạn nhập.',
            ],
        ];

        $contacts = [
            ['icon' => '📧', 'label' => 'Email hỗ trợ', 'value' => 'support@staysmart.vn'],
            ['icon' => '📞', 'label' => 'Hotline 24/7', 'value' => '1900 6868'],
            ['icon' => '💬', 'label' => 'Trợ lý AI', 'value' => 'Click bong bóng Smarty góc phải'],
            ['icon' => '🏢', 'label' => 'Văn phòng', 'value' => '15 Ngô Quyền, Hoàn Kiếm, Hà Nội'],
        ];

        return Inertia::render('Support', [
            'faqs' => $faqs,
            'contacts' => $contacts,
        ]);
    }

    public function submit(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150'],
            'subject' => ['required', 'string', 'max:200'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        // Demo: just log it. In production, send email or store in DB
        logger()->info('Support contact', $request->only(['name', 'email', 'subject', 'message']));

        return back()->with('success', 'Cảm ơn bạn! Chúng tôi sẽ phản hồi qua email trong vòng 24h.');
    }
}
