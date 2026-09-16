<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create document_types table
        Schema::create('document_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Create document_type_fields table
        Schema::create('document_type_fields', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('document_type_id')->constrained('document_types')->cascadeOnDelete();
            $table->string('field_name', 100);
            $table->string('field_label', 150);
            $table->enum('field_type', ['text', 'email', 'textarea', 'number', 'date', 'select']);
            $table->json('field_options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->string('validation_rules', 255)->nullable();
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });

        // Migrate existing document_requests data
        // First, create the default document type
        $documentTypeId = DB::table('document_types')->insertGetId([
            'name' => 'Document Fee Request Form',
            'description' => 'General document request form',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Add document_type_id column to document_requests
        Schema::table('document_requests', function (Blueprint $table): void {
            $table->foreignId('document_type_id')->nullable()->after('user_id')->constrained('document_types')->restrictOnDelete();
        });

        // Update existing requests to use the new document type
        DB::table('document_requests')->update(['document_type_id' => $documentTypeId]);

        // Drop the old document_type column
        Schema::table('document_requests', function (Blueprint $table): void {
            $table->dropColumn('document_type');
        });
    }

    public function down(): void
    {
        // Add back document_type column
        Schema::table('document_requests', function (Blueprint $table): void {
            $table->string('document_type', 100)->after('user_id');
        });

        // Copy data back
        DB::statement('
            UPDATE document_requests dr
            INNER JOIN document_types dt ON dr.document_type_id = dt.id
            SET dr.document_type = dt.name
        ');

        // Drop foreign key and column
        Schema::table('document_requests', function (Blueprint $table): void {
            $table->dropForeign(['document_type_id']);
            $table->dropColumn('document_type_id');
        });

        // Drop tables
        Schema::dropIfExists('document_type_fields');
        Schema::dropIfExists('document_types');
    }
};
