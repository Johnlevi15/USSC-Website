<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lost_found_items', function (Blueprint $table): void {
            $table->string('approval_status', 20)->default('pending')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('lost_found_items', function (Blueprint $table): void {
            $table->dropColumn('approval_status');
        });
    }
};
