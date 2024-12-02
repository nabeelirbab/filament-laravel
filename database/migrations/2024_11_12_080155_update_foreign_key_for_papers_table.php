<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('papers', function (Blueprint $table) {
            $table->dropForeign(['associate_editor_id']); // Drop the existing constraint
            $table->foreign('associate_editor_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete(); // Add the new constraint
        });
    }

    public function down(): void
    {
        Schema::table('papers', function (Blueprint $table) {
            $table->dropForeign(['associate_editor_id']); // Drop the modified constraint
            $table->foreign('associate_editor_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete(); // Restore the original behavior
        });
    }
};
