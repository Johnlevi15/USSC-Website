<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('document_requests', 'admin_remarks')) {
            Schema::table('document_requests', function (Blueprint $table): void {
                $table->text('admin_remarks')->nullable()->after('reviewed_by');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('document_requests', 'admin_remarks')) {
            Schema::table('document_requests', function (Blueprint $table): void {
                $table->dropColumn('admin_remarks');
            });
        }
    }
};
