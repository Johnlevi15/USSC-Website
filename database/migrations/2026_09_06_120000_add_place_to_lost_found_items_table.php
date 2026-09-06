<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('lost_found_items', 'place')) {
            Schema::table('lost_found_items', function (Blueprint $table) {
                $table->string('place', 255)->nullable()->after('status');
            });
        }
    }

    public function down(): void
    {
        Schema::table('lost_found_items', function (Blueprint $table) {
            $table->dropColumn('place');
        });
    }
};
