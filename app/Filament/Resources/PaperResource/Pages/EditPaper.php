<?php

namespace App\Filament\Resources\PaperResource\Pages;

use App\Filament\Resources\PaperResource;
use App\Mail\Visualbuilder\EmailTemplates\MajorRevisionNotification;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Visualbuilder\EmailTemplates\Contracts\TokenHelperInterface;

class EditPaper extends EditRecord
{
    protected static string $resource = PaperResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function afterSave(): void
    {
        $record = $this->record;

        // Check if the status is "major_revision"
        if ($record->status === 'ready_for_major_revision') {
            $associateEditor = $record->associateEditor;
            $tokenHelper = app(TokenHelperInterface::class);
            if ($associateEditor) {
                // Prepare email data
                $paper = (object) [
                    'name' => $associateEditor->name,
                    'title' => $record->title,
                    'author' => $record->author->name,
                    'email' => $associateEditor->email,
                    'revision' => $record->revision_comment,
                    'link' =>  url(Storage::url($record->revision_file)),
                ];
                // Send the email
                Mail::to($associateEditor->email)->send(new MajorRevisionNotification($paper, $tokenHelper));
            }
        }
    }
}
