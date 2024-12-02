<?php

namespace App\Mail\Visualbuilder\EmailTemplates;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Visualbuilder\EmailTemplates\Contracts\TokenHelperInterface;
use Visualbuilder\EmailTemplates\Traits\BuildGenericEmail;

class PaperAssignedNotification extends Mailable
{
    use Queueable;
    use SerializesModels;
    use BuildGenericEmail;

    public $template = 'paper-assigned-notification';
    public $paper;
    public $sendTo;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($paper, TokenHelperInterface $tokenHelper)
    {
        $this->paper = $paper;
        $this->sendTo = $paper->email;
        $this->initializeTokenHelper($tokenHelper);
    }
}
