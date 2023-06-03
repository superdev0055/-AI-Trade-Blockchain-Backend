<?php

namespace App\Mail;

use App\Models\PledgeProfits;
use App\Models\Users;
use App\NewServices\CoinServices;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PledgeProfitNoticeMail extends Mailable
{
    use Queueable, SerializesModels;

    public Users $user;
    public PledgeProfits $profit;

    /**
     * @param Users $user
     * @param PledgeProfits $profit
     * @throws Exception
     */
    public function __construct(Users $user, PledgeProfits $profit)
    {
        $this->user = $user;
        $this->profit = $profit;
        $price = CoinServices::GetPrice('usdc');

        $profit->actual_income_usd = round($profit->actual_income * $price, 2);
        $profit->actual_income = round($profit->actual_income, 6);

        $this->profit = $profit;
    }

    /**
     * @return PledgeProfitNoticeMail
     */
    public function build(): PledgeProfitNoticeMail
    {
        return $this->view('emails.pledge.profits');
    }
}
