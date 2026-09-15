<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('creators', function (Blueprint $table) {
            // The creator's own choice to stay out of the directory. Separate from `status`,
            // which is the admin's moderation switch and must not be undone by the creator.
            $table->boolean('is_listed')->default(true)->after('status')->index();
        });
    }

    public function down(): void
    {
        Schema::table('creators', function (Blueprint $table) {
            $table->dropColumn('is_listed');
        });
    }
};
