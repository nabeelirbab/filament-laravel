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
        Schema::table('papers', function (Blueprint $table) {
            $table->text('revision_comment')->nullable()->after('status');
            $table->string('revision_file')->nullable()->after('revision_comment');
        });
    }

    public function down(): void
    {
        Schema::table('papers', function (Blueprint $table) {
            $table->dropColumn('revision_comment');
            $table->dropColumn('revision_file');
        });
    }
};
