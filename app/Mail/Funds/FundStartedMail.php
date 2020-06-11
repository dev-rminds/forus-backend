<?php

namespace App\Mail\Funds;

use App\Mail\ImplementationMail;
use App\Services\Forus\Notification\EmailFrom;
use Illuminate\Mail\Mailable;

/**
 * Class FundStartedMail
 * @package App\Mail\Funds
 */
class FundStartedMail extends ImplementationMail
{
    private $fund_name;
    private $sponsor_name;

    public function __construct(
        string $fund_name,
        string $sponsor_name,
        ?EmailFrom $emailFrom
    ) {
        $this->emailFrom    = $emailFrom;
        $this->fund_name    = $fund_name;
        $this->sponsor_name = $sponsor_name;
    }

    public function build(): Mailable
    {
        return $this->buildBase()
            ->subject(mail_trans('fund_started.title', [
                'fund_name' => $this->fund_name
            ]))
            ->view('emails.funds.fund_started', [
                'fund_name'      => $this->fund_name,
                'sponsor_name'   => $this->sponsor_name
            ]);
    }
}
