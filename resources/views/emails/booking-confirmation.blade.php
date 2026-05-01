<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Xác nhận đặt phòng STAY-SMART</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f5f1e8; margin: 0; padding: 32px 0; color: #0b1410;">
    <div style="max-width: 600px; margin: 0 auto; background: #fbf8f1; border-radius: 16px; overflow: hidden; box-shadow: 0 8px 24px rgba(11,20,16,.08);">
        <div style="background: #0a3d2e; color: #fbf8f1; padding: 28px 32px;">
            <div style="font-size: 22px; font-weight: 600; letter-spacing: -0.02em;">STAY-SMART<span style="color: #b85c3c;">.</span></div>
            <div style="font-size: 13px; color: #6ed1a8; margin-top: 4px;">Trợ lý đặt phòng AI · Hà Nội</div>
        </div>
        <div style="padding: 32px;">
            <h1 style="font-size: 24px; font-weight: 500; margin: 0 0 8px;">Xin chào {{ $booking->guest_name }},</h1>
            <p style="font-size: 15px; color: #4a5751; margin: 0 0 24px; line-height: 1.6;">Đặt phòng của bạn đã được xác nhận thành công. Dưới đây là chi tiết đơn:</p>

            <div style="background: #14724f; color: #fbf8f1; padding: 16px 20px; border-radius: 12px; text-align: center; margin-bottom: 24px;">
                <div style="font-size: 11px; opacity: 0.7; text-transform: uppercase; letter-spacing: 0.05em;">Mã đặt phòng</div>
                <div style="font-size: 26px; font-weight: 600; letter-spacing: 0.05em; margin-top: 4px;">{{ $booking->booking_code }}</div>
            </div>

            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                <tr>
                    <td colspan="2" style="padding: 8px 0; border-bottom: 1px solid #d8ddd8;">
                        <strong style="font-size: 18px;">{{ $hotel->name }}</strong><br>
                        <small style="color: #4a5751;">{{ $hotel->address }}</small>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 0; color: #4a5751; font-size: 13px; width: 50%;">Loại phòng</td>
                    <td style="padding: 12px 0; font-weight: 500; text-align: right;">{{ $room->name }}</td>
                </tr>
                <tr>
                    <td style="padding: 12px 0; color: #4a5751; font-size: 13px;">Nhận phòng</td>
                    <td style="padding: 12px 0; font-weight: 500; text-align: right;">{{ $booking->checkin_date->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td style="padding: 12px 0; color: #4a5751; font-size: 13px;">Trả phòng</td>
                    <td style="padding: 12px 0; font-weight: 500; text-align: right;">{{ $booking->checkout_date->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td style="padding: 12px 0; color: #4a5751; font-size: 13px;">Số đêm · Số khách</td>
                    <td style="padding: 12px 0; font-weight: 500; text-align: right;">{{ $booking->nights }} đêm · {{ $booking->guests_count }} khách</td>
                </tr>
                @if($payment)
                <tr>
                    <td style="padding: 12px 0; color: #4a5751; font-size: 13px;">Phương thức thanh toán</td>
                    <td style="padding: 12px 0; font-weight: 500; text-align: right;">{{ strtoupper($payment->method) }}</td>
                </tr>
                <tr>
                    <td style="padding: 12px 0; color: #4a5751; font-size: 13px;">Mã giao dịch</td>
                    <td style="padding: 12px 0; font-weight: 500; text-align: right; font-family: monospace; font-size: 12px;">{{ $payment->transaction_ref }}</td>
                </tr>
                @endif
            </table>

            <div style="background: #efe9dc; padding: 16px 20px; border-radius: 12px;">
                <div style="display: flex; justify-content: space-between; padding: 4px 0; font-size: 14px; color: #4a5751;">
                    <span>Tạm tính ({{ $booking->nights }} đêm)</span>
                    <span>{{ number_format($booking->subtotal, 0, ',', '.') }}đ</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 4px 0; font-size: 14px; color: #4a5751;">
                    <span>Thuế &amp; phí (10%)</span>
                    <span>{{ number_format($booking->tax, 0, ',', '.') }}đ</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 12px 0 4px; border-top: 1px solid #d8ddd8; margin-top: 8px; font-size: 18px; font-weight: 600;">
                    <span>Tổng cộng</span>
                    <span style="color: #0a3d2e;">{{ number_format($booking->total_amount, 0, ',', '.') }}đ</span>
                </div>
            </div>

            @if($booking->special_requests)
            <div style="margin-top: 20px; padding: 14px 18px; background: #f5f1e8; border-left: 3px solid #c4965a; border-radius: 8px;">
                <div style="font-size: 12px; color: #4a5751; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Yêu cầu đặc biệt</div>
                <div style="font-size: 14px;">{{ $booking->special_requests }}</div>
            </div>
            @endif

            <p style="font-size: 13px; color: #4a5751; margin-top: 28px; line-height: 1.6;">
                Vui lòng giữ email này như xác nhận đặt phòng. Khách sạn sẽ liên hệ qua số điện thoại {{ $booking->guest_phone }} nếu có thay đổi.<br><br>
                Chúc bạn có một kỳ nghỉ tuyệt vời tại Hà Nội!<br>
                <strong style="color: #0a3d2e;">Đội ngũ STAY-SMART</strong>
            </p>
        </div>
        <div style="background: #efe9dc; padding: 16px 32px; text-align: center; font-size: 11px; color: #8a9893;">
            Email tự động · Vui lòng không phản hồi
        </div>
    </div>
</body>
</html>
