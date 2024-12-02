<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('papers', function (Blueprint $table) {
            // Drop the existing foreign key constraint first
            $table->dropForeign(['author_id']);

            // Add the new foreign key constraint with nullOnDelete
            $table->foreign('author_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete(); // Use cascadeOnDelete() if you want cascading deletes
        });
    }

    public function down(): void
    {
        Schema::table('papers', function (Blueprint $table) {
            // Drop the modified foreign key constraint
            $table->dropForeign(['author_id']);

            // Restore the original foreign key constraint
            $table->foreign('author_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();
        });
    }
};
