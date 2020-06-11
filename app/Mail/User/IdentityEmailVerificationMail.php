<?php

namespace App\Mail\User;

use App\Mail\ImplementationMail;
use App\Services\Forus\Notification\EmailFrom;
use Illuminate\Mail\Mailable;

/**
 * Class EmailActivationMail
 * @package App\Mail\User
 */
class IdentityEmailVerificationMail extends ImplementationMail
{
    private $link;

    /**
     * IdentityEmailVerificationMail constructor.
     * @param string $link
     * @param EmailFrom $emailFrom
     */
    public function __construct(string $link, ?EmailFrom $emailFrom) {
        parent::__construct($emailFrom);
        $this->link = $link;
    }

    /**
     * @return ImplementationMail
     */
    public function build(): Mailable
    {
        return parent::build()
            ->subject(mail_trans('identity_email_verification.title'))
            ->view('emails.identity-email-verification', [
                'link' => $this->link
            ]);
    }
}
