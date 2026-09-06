<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->foreignId('admin_id')->primary()->constrained('users')->cascadeOnDelete();
            $table->string('password_hash');
        });

        Schema::create('document_requests', function (Blueprint $table) {
            $table->increments('request_id');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('document_type', 100);
            $table->string('status', 20);
            $table->foreignId('reviewed_by')->nullable()->constrained('admins', 'admin_id')->nullOnDelete();
            $table->timestamp('submitted_at')->useCurrent();
        });

        Schema::create('document', function (Blueprint $table) {
            $table->unsignedInteger('request_id');
            $table->string('field_name', 100);
            $table->string('field_value', 500);
            $table->primary(['request_id', 'field_name']);
            $table->foreign('request_id')->references('request_id')->on('document_requests')->cascadeOnDelete();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->increments('event_id');
            $table->string('title', 150);
            $table->date('event_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->foreignId('created_by')->constrained('admins', 'admin_id')->restrictOnDelete();
        });

        Schema::create('event_calendar', function (Blueprint $table) {
            $table->foreignId('admin_id')->constrained('admins', 'admin_id')->cascadeOnDelete();
            $table->unsignedInteger('event_id');
            $table->date('calendar_date');
            $table->string('view_type', 20);
            $table->primary(['admin_id', 'event_id']);
            $table->foreign('event_id')->references('event_id')->on('events')->cascadeOnDelete();
        });

        Schema::create('lost_found_items', function (Blueprint $table) {
            $table->increments('item_id');
            $table->foreignId('posted_by')->constrained('users')->cascadeOnDelete();
            $table->string('item_name', 150);
            $table->string('category', 100);
            $table->text('description');
            $table->string('status', 20);
            $table->foreignId('reviewed_by')->nullable()->constrained('admins', 'admin_id')->nullOnDelete();
        });

        Schema::create('email_notifications', function (Blueprint $table) {
            $table->increments('email_id');
            $table->unsignedInteger('request_id');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('sent_by')->nullable()->constrained('admins', 'admin_id')->nullOnDelete();
            $table->string('status', 20);
            $table->timestamp('sent_at')->nullable();
            $table->foreign('request_id')->references('request_id')->on('document_requests')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_notifications');
        Schema::dropIfExists('lost_found_items');
        Schema::dropIfExists('event_calendar');
        Schema::dropIfExists('events');
        Schema::dropIfExists('document');
        Schema::dropIfExists('document_requests');
        Schema::dropIfExists('admins');
    }
};
