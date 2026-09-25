<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lost_found_items', function (Blueprint $table): void {
            $table->text('admin_remarks')->nullable()->after('approval_status');
            $table->timestamp('archived_at')->nullable()->after('reviewed_by');
            $table->foreignId('archived_by')->nullable()->after('archived_at')->constrained('admins', 'admin_id')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lost_found_items', function (Blueprint $table): void {
            $table->dropForeign(['archived_by']);
            $table->dropColumn(['admin_remarks', 'archived_at', 'archived_by']);
        });
    }
};
