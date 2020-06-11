<?php

namespace App\Mail\Funds\Forus;

use App\Mail\ImplementationMail;
use App\Services\Forus\Notification\EmailFrom;
use Illuminate\Mail\Mailable;

/**
 * Class FundCreatedMail
 * @package App\Mail\Funds\Forus
 */
class ForusFundCreated extends ImplementationMail
{
    private $fundName;
    private $organizationName;

    public function __construct(
        string $fundName,
        string $organizationName,
        ?EmailFrom $emailFrom
    ) {
        parent::__construct($emailFrom);

        $this->fundName = $fundName;
        $this->organizationName = $organizationName;
    }

    public function build(): Mailable
    {
        return parent::build()
            ->subject(mail_trans('forus/fund_created.title', [
                'fund_name' => $this->fundName
            ]))
            ->view('emails.forus.new_fund_created', [
                'fund_name' => $this->fundName,
                'organization_name' => $this->organizationName
            ]);
    }
}
