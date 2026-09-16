<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password')->nullable();
                $table->rememberToken();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table): void {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        if (! Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table): void {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }

        if (! Schema::hasTable('cache')) {
            Schema::create('cache', function (Blueprint $table): void {
                $table->string('key')->primary();
                $table->mediumText('value');
                $table->bigInteger('expiration')->index();
            });
        }

        if (! Schema::hasTable('cache_locks')) {
            Schema::create('cache_locks', function (Blueprint $table): void {
                $table->string('key')->primary();
                $table->string('owner');
                $table->bigInteger('expiration')->index();
            });
        }

        if (! Schema::hasTable('jobs')) {
            Schema::create('jobs', function (Blueprint $table): void {
                $table->id();
                $table->string('queue')->index();
                $table->longText('payload');
                $table->unsignedSmallInteger('attempts');
                $table->unsignedInteger('reserved_at')->nullable();
                $table->unsignedInteger('available_at');
                $table->unsignedInteger('created_at');
            });
        }

        if (! Schema::hasTable('job_batches')) {
            Schema::create('job_batches', function (Blueprint $table): void {
                $table->string('id')->primary();
                $table->string('name');
                $table->integer('total_jobs');
                $table->integer('pending_jobs');
                $table->integer('failed_jobs');
                $table->longText('failed_job_ids');
                $table->mediumText('options')->nullable();
                $table->integer('cancelled_at')->nullable();
                $table->integer('created_at');
                $table->integer('finished_at')->nullable();
            });
        }

        if (! Schema::hasTable('failed_jobs')) {
            Schema::create('failed_jobs', function (Blueprint $table): void {
                $table->id();
                $table->string('uuid')->unique();
                $table->string('connection');
                $table->string('queue');
                $table->longText('payload');
                $table->longText('exception');
                $table->timestamp('failed_at')->useCurrent();
                $table->index(['connection', 'queue', 'failed_at']);
            });
        }

        if (! Schema::hasTable('admins')) {
            Schema::create('admins', function (Blueprint $table): void {
                $table->id('admin_id');
                $table->string('name');
                $table->string('email')->unique();
                $table->string('password_hash');
                $table->rememberToken();
            });
        }

        if (! Schema::hasTable('document_requests')) {
            Schema::create('document_requests', function (Blueprint $table): void {
                $table->increments('request_id');
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('document_type', 100);
                $table->string('status', 20);
                $table->foreignId('reviewed_by')->nullable()->constrained('admins', 'admin_id')->nullOnDelete();
                $table->timestamp('submitted_at')->useCurrent();
            });
        }

        if (! Schema::hasTable('document')) {
            Schema::create('document', function (Blueprint $table): void {
                $table->unsignedInteger('request_id');
                $table->string('field_name', 100);
                $table->string('field_value', 500);
                $table->primary(['request_id', 'field_name']);
                $table->foreign('request_id')->references('request_id')->on('document_requests')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('events')) {
            Schema::create('events', function (Blueprint $table): void {
                $table->increments('event_id');
                $table->string('title', 150);
                $table->text('description')->nullable();
                $table->date('event_date');
                $table->time('start_time');
                $table->time('end_time');
                $table->foreignId('created_by')->constrained('admins', 'admin_id')->restrictOnDelete();
            });
        }

        if (! Schema::hasTable('event_calendar')) {
            Schema::create('event_calendar', function (Blueprint $table): void {
                $table->foreignId('admin_id')->constrained('admins', 'admin_id')->cascadeOnDelete();
                $table->unsignedInteger('event_id');
                $table->date('calendar_date');
                $table->string('view_type', 20);
                $table->primary(['admin_id', 'event_id']);
                $table->foreign('event_id')->references('event_id')->on('events')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('lost_found_items')) {
            Schema::create('lost_found_items', function (Blueprint $table): void {
                $table->increments('item_id');
                $table->foreignId('posted_by')->constrained('users')->cascadeOnDelete();
                $table->string('item_name', 150);
                $table->string('category', 100);
                $table->text('description');
                $table->string('image_path')->nullable();
                $table->string('status', 20);
                $table->string('approval_status', 20)->default('pending');
                $table->timestamp('submitted_at')->nullable()->useCurrent();
                $table->string('place')->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('admins', 'admin_id')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('email_notifications')) {
            Schema::create('email_notifications', function (Blueprint $table): void {
                $table->increments('email_id');
                $table->unsignedInteger('request_id');
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('sent_by')->nullable()->constrained('admins', 'admin_id')->nullOnDelete();
                $table->string('status', 20);
                $table->timestamp('sent_at')->nullable();
                $table->foreign('request_id')->references('request_id')->on('document_requests')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('admin_activity_logs')) {
            Schema::create('admin_activity_logs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('admin_id')->constrained('admins', 'admin_id')->cascadeOnDelete();
                $table->string('action', 50);
                $table->string('description', 500);
                $table->string('subject_type', 100)->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->timestamps();
                $table->index(['subject_type', 'subject_id']);
                $table->index(['admin_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('admin_activity_logs');
        Schema::dropIfExists('email_notifications');
        Schema::dropIfExists('lost_found_items');
        Schema::dropIfExists('event_calendar');
        Schema::dropIfExists('events');
        Schema::dropIfExists('document');
        Schema::dropIfExists('document_requests');
        Schema::dropIfExists('admins');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        Schema::enableForeignKeyConstraints();
    }
};
