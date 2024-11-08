<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateDecisionColumnInReviewsTable extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE reviews MODIFY decision ENUM(
            'pending', 
            'accepted', 
            'declined', 
            'in_progress', 
            'completed', 
            'approved', 
            'minor_revision', 
            'major_revision', 
            'rejected', 
            'revision_requested' -- New value
        ) DEFAULT 'pending'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE reviews MODIFY decision ENUM(
            'pending', 
            'accepted', 
            'declined', 
            'in_progress', 
            'completed', 
            'approved', 
            'minor_revision', 
            'major_revision', 
            'rejected'
        ) DEFAULT 'pending'");
    }
}
