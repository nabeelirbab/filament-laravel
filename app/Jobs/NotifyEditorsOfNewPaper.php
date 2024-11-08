<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Paper;
use App\Notifications\PaperSubmittedNotification;
use App\Notifications\AuthorPaperSubmissionConfirmation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyEditorsOfNewPaper implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $paper;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Paper $paper)
    {
        $this->paper = $paper;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Notify all editors and associate editors
        $editors = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['editor']);
        })->get();

        foreach ($editors as $editor) {
            $editor->notify(new PaperSubmittedNotification($this->paper));
        }

        // Notify the author with the submission confirmation email
        $author = $this->paper->author; // Assuming the Paper model has an `author` relationship
        if ($author) {
            $author->notify(new AuthorPaperSubmissionConfirmation($this->paper));
        }
    }
}
