<?php

namespace App\Mail\Visualbuilder\EmailTemplates;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Visualbuilder\EmailTemplates\Contracts\TokenHelperInterface;
use Visualbuilder\EmailTemplates\Traits\BuildGenericEmail;

class PaperSubmissionConfirmation extends Mailable
{
    use Queueable;
    use SerializesModels;
    use BuildGenericEmail;

    public $template = 'paper-submission-confirmation';
    public $paper;
    public $sendTo;
    public $name;
    public $submissionDate;

    /**
     * Create a new message instance.
     *
     * @param $paper
     * @param TokenHelperInterface $tokenHelper
     */
    public function __construct($paper, TokenHelperInterface $tokenHelper)
    {
        $this->paper = $paper;
        $this->sendTo = $paper->email;

        // Initialize token helper for templating
        $this->initializeTokenHelper($tokenHelper);
    }
}
