<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Temporarily drop foreign key constraints that reference admin_id
        $foreignKeys = [
            'document_requests' => 'document_requests_reviewed_by_foreign',
            'events' => 'events_created_by_foreign',
            'event_calendar' => 'event_calendar_admin_id_foreign',
            'lost_found_items' => 'lost_found_items_reviewed_by_foreign',
            'email_notifications' => 'email_notifications_sent_by_foreign',
            'admin_activity_logs' => 'admin_activity_logs_admin_id_foreign',
        ];

        foreach ($foreignKeys as $table => $constraint) {
            if (Schema::hasTable($table)) {
                try {
                    Schema::table($table, function (Blueprint $table) use ($constraint): void {
                        $table->dropForeign([$constraint]);
                    });
                } catch (\Exception $e) {
                    // Foreign key might not exist or have different name
                    // Try to drop by column name instead
                    $columnMap = [
                        'document_requests_reviewed_by_foreign' => 'reviewed_by',
                        'events_created_by_foreign' => 'created_by',
                        'event_calendar_admin_id_foreign' => 'admin_id',
                        'lost_found_items_reviewed_by_foreign' => 'reviewed_by',
                        'email_notifications_sent_by_foreign' => 'sent_by',
                        'admin_activity_logs_admin_id_foreign' => 'admin_id',
                    ];

                    if (isset($columnMap[$constraint])) {
                        try {
                            Schema::table($table, function (Blueprint $table) use ($columnMap, $constraint): void {
                                $table->dropForeign([$columnMap[$constraint]]);
                            });
                        } catch (\Exception $e2) {
                            // Continue if foreign key doesn't exist
                        }
                    }
                }
            }
        }

        // Step 2: Add remember_token if it doesn't exist
        if (!Schema::hasColumn('admins', 'remember_token')) {
            Schema::table('admins', function (Blueprint $table): void {
                $table->rememberToken()->after('password_hash');
            });
        }

        // Step 3: Make admin_id auto-increment
        DB::statement('ALTER TABLE admins MODIFY admin_id BIGINT UNSIGNED AUTO_INCREMENT');

        // Step 4: Add unique constraint to email if it doesn't exist
        $indexes = DB::select("SHOW INDEXES FROM admins WHERE Column_name = 'email'");
        if (empty($indexes)) {
            Schema::table('admins', function (Blueprint $table): void {
                $table->unique('email');
            });
        }

        // Step 5: Re-add foreign key constraints (now referencing the independent admins table)
        $tables = [
            'document_requests' => ['reviewed_by', 'admin_id', 'nullOnDelete'],
            'events' => ['created_by', 'admin_id', 'restrictOnDelete'],
            'event_calendar' => ['admin_id', 'admin_id', 'cascadeOnDelete'],
            'lost_found_items' => ['reviewed_by', 'admin_id', 'nullOnDelete'],
            'email_notifications' => ['sent_by', 'admin_id', 'nullOnDelete'],
            'admin_activity_logs' => ['admin_id', 'admin_id', 'cascadeOnDelete'],
        ];

        foreach ($tables as $table => $config) {
            if (Schema::hasTable($table)) {
                [$column, $references, $onDelete] = $config;
                Schema::table($table, function (Blueprint $table) use ($column, $references, $onDelete): void {
                    $table->foreign($column)->references($references)->on('admins')->$onDelete();
                });
            }
        }
    }

    public function down(): void
    {
        throw new \LogicException('This migration cannot be safely reversed. Admins are now independent entities.');
    }
};
