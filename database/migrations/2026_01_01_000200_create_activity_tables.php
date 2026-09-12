<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->foreignId('permit_id')->nullable()->constrained('vehicle_permits')->nullOnDelete();
            $table->enum('type', ['in', 'out']);
            $table->enum('kind', ['normal', 're_entry', 'manual'])->default('normal');
            $table->timestamp('scanned_at');
            $table->foreignId('scanned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('gate_id')->nullable()->constrained('gates')->nullOnDelete();
            $table->string('device_info')->nullable();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('pair_id')->nullable();
            $table->boolean('is_early_leave')->default(false);
            $table->boolean('is_offline_sync')->default(false);
            $table->uuid('offline_id')->nullable()->unique();
            $table->timestamps();

            $table->index(['student_id', 'scanned_at']);
            $table->index('scanned_at');
        });

        Schema::create('scan_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('qr_token_raw')->nullable();
            $table->foreignId('permit_id')->nullable()->constrained('vehicle_permits')->nullOnDelete();
            $table->enum('result', ['ok', 'denied_blocked', 'denied_expired', 'denied_revoked', 'not_found', 'duplicate', 'too_early']);
            $table->foreignId('scanned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('created_at');
            $table->index('result');
        });

        Schema::create('violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('category', [
                'tidak_pakai_helm', 'berboncengan_tiga', 'knalpot_bising',
                'parkir_sembarangan', 'tidak_punya_sim', 'kebut', 'lainnya',
            ]);
            $table->text('description')->nullable();
            $table->smallInteger('points')->default(0);
            $table->timestamp('occurred_at');
            $table->string('evidence_photo_path')->nullable();
            $table->timestamps();

            $table->index('occurred_at');
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['subject_type', 'subject_id']);
            $table->index('created_at');
        });

        Schema::create('data_change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('field');
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('data_change_requests');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('violations');
        Schema::dropIfExists('scan_attempts');
        Schema::dropIfExists('attendance_logs');
    }
};
