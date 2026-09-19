<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE document_type_fields MODIFY field_type ENUM('text', 'email', 'textarea', 'number', 'date', 'select', 'checkbox') NOT NULL");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE document_type_fields MODIFY field_type ENUM('text', 'email', 'textarea', 'number', 'date', 'select') NOT NULL");
    }
};
