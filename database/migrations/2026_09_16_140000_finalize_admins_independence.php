<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('admins', 'remember_token')) {
            Schema::table('admins', function (Blueprint $table): void {
                $table->rememberToken()->after('password_hash');
            });
        }

        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        $foreignKeys = [
            'admins' => 'admins_admin_id_foreign',
            'document_requests' => 'document_requests_reviewed_by_foreign',
            'events' => 'events_created_by_foreign',
            'event_calendar' => 'event_calendar_admin_id_foreign',
            'lost_found_items' => 'lost_found_items_reviewed_by_foreign',
            'email_notifications' => 'email_notifications_sent_by_foreign',
            'admin_activity_logs' => 'admin_activity_logs_admin_id_foreign',
        ];

        foreach ($foreignKeys as $table => $constraint) {
            $this->dropForeignKeyIfExists($table, $constraint);
        }

        if (! $this->columnIsAutoIncrement('admins', 'admin_id')) {
            DB::statement('ALTER TABLE admins MODIFY admin_id BIGINT UNSIGNED AUTO_INCREMENT');
        }

        if (! $this->columnHasUniqueIndex('admins', 'email')) {
            Schema::table('admins', function (Blueprint $table): void {
                $table->unique('email');
            });
        }

        $tables = [
            'document_requests' => ['reviewed_by', 'admin_id', 'nullOnDelete'],
            'events' => ['created_by', 'admin_id', 'restrictOnDelete'],
            'event_calendar' => ['admin_id', 'admin_id', 'cascadeOnDelete'],
            'lost_found_items' => ['reviewed_by', 'admin_id', 'nullOnDelete'],
            'email_notifications' => ['sent_by', 'admin_id', 'nullOnDelete'],
            'admin_activity_logs' => ['admin_id', 'admin_id', 'cascadeOnDelete'],
        ];

        foreach ($tables as $table => $config) {
            [$column, $references, $onDelete] = $config;
            $constraint = "{$table}_{$column}_foreign";

            if (
                Schema::hasTable($table)
                && Schema::hasColumn($table, $column)
                && ! $this->foreignKeyExists($table, $constraint)
            ) {
                Schema::table($table, function (Blueprint $table) use ($column, $references, $onDelete): void {
                    $table->foreign($column)->references($references)->on('admins')->$onDelete();
                });
            }
        }
    }

    public function down(): void
    {
        throw new LogicException('This migration cannot be safely reversed. Admins are now independent entities.');
    }

    private function dropForeignKeyIfExists(string $table, string $constraint): void
    {
        if (! Schema::hasTable($table) || ! $this->foreignKeyExists($table, $constraint)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($constraint): void {
            $table->dropForeign($constraint);
        });
    }

    private function foreignKeyExists(string $table, string $constraint): bool
    {
        $result = DB::selectOne(
            <<<'SQL'
            SELECT COUNT(*) AS aggregate
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND CONSTRAINT_NAME = ?
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            SQL,
            [$table, $constraint],
        );

        return ((int) $result->aggregate) > 0;
    }

    private function columnIsAutoIncrement(string $table, string $column): bool
    {
        $result = DB::selectOne(
            <<<'SQL'
            SELECT EXTRA
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
            SQL,
            [$table, $column],
        );

        return str_contains(strtolower((string) $result?->EXTRA), 'auto_increment');
    }

    private function columnHasUniqueIndex(string $table, string $column): bool
    {
        $indexes = DB::select("SHOW INDEXES FROM {$table} WHERE Column_name = ?", [$column]);

        return collect($indexes)->contains(fn (object $index): bool => (int) $index->Non_unique === 0);
    }
};
