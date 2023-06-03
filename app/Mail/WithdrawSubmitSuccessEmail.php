<?php

namespace App\Mail;

use App\Models\Assets;
use App\Models\Users;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WithdrawSubmitSuccessEmail extends Mailable
{
    use Queueable, SerializesModels;

    private Users $user;
    private Assets $asset;
    private float $usdPrice;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Users $user, Assets $asset, float $usdPrice)
    {
        $this->user = $user;
        $this->asset = $asset;
        $this->usdPrice = $usdPrice;
    }

    /**
     * Get the message envelope.
     *
     * @return Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Withdraw Submit Success Email',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return Content
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.withdraw.success',
            with: [
                'user' => $this->user,
                'asset' => $this->asset,
                'usdPrice' => $this->usdPrice,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments(): array
    {
        return [];
    }
}
