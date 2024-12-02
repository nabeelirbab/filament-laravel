<?php

namespace App\Mail\Visualbuilder\EmailTemplates;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Visualbuilder\EmailTemplates\Contracts\TokenHelperInterface;
use Visualbuilder\EmailTemplates\Traits\BuildGenericEmail;

class ReviewCompleted extends Mailable
{
    use Queueable;
    use SerializesModels;
    use BuildGenericEmail;

    public $template = 'review-completed';
    public $review;
    public $sendTo;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($review, TokenHelperInterface $tokenHelper)
    {
        $this->review = $review;
        $this->sendTo = $review->email;
        $this->initializeTokenHelper($tokenHelper);
    }
}
