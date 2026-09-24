<?php

namespace App\Mail;

use App\Models\Receipt;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public Receipt $receipt;

    /**
     * Create a new message instance.
     */
    public function __construct(Receipt $receipt)
    {
        $this->receipt = $receipt;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $typeLabel = $this->receipt->type_label;
        $receiptNum = $this->receipt->receipt_number;

        return new Envelope(
            subject: "Receipt for your {$typeLabel} {$receiptNum} — RideMyCars",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-receipt',
            with: [
                'receipt' => $this->receipt,
                'snapshot' => $this->receipt->snapshot_data ?? [],
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $pdfPath = $this->receipt->getPdfAbsolutePath();

        if ($pdfPath && file_exists($pdfPath)) {
            return [
                Attachment::fromPath($pdfPath)
                    ->as("Receipt_{$this->receipt->receipt_number}.pdf")
                    ->withMime('application/pdf'),
            ];
        }

        return [];
    }
}
