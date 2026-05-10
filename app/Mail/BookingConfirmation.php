<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking) {}

    public function envelope(): Envelope
    {
        $label = $this->booking->booking_type === 'combo' ? 'đặt tour' : 'đặt phòng';

        return new Envelope(
            subject: "STAY-SMART · Xác nhận {$label} {$this->booking->booking_code}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-confirmation',
            with: [
                'booking' => $this->booking,
                'hotel' => $this->booking->hotel,
                'room' => $this->booking->room,
                'combo' => $this->booking->combo(),
                'payment' => $this->booking->payment,
            ],
        );
    }
}
