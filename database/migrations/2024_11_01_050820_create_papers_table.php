<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('papers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('abstract');
            $table->string('keywords');
            $table->json('fields'); // Storing multiple checkboxes as JSON
            $table->string('file_path');
            $table->foreignId('author_id')->constrained('users');
            $table->enum('status', [
                'submitted',
                'under_review',
                'ready_for_minor_revision',
                'ready_for_major_revision',
                'resubmitted',
                'ready_for_decision',
                'accepted',
                'rejected',
            ])->default('submitted');
            $table->foreignId('associate_editor_id')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('papers');
    }
};
