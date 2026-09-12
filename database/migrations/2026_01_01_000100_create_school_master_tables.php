<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('name');           // 2026/2027
            $table->string('code', 8)->unique(); // 2627
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('gates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->string('nisn', 10)->unique();
            $table->string('nis')->nullable();
            $table->string('name');
            $table->enum('gender', ['L', 'P'])->default('L');
            $table->date('birth_date')->nullable();
            $table->string('class_room');
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('photo_path')->nullable();
            $table->enum('status', ['active', 'blocked', 'graduated', 'inactive'])->default('active');
            $table->text('blocked_reason')->nullable();
            $table->string('blocked_type')->nullable();
            $table->timestamp('blocked_at')->nullable();
            $table->date('blocked_until')->nullable();
            $table->foreignId('blocked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('class_room');
            $table->index('status');
        });

        Schema::create('parents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('phone')->unique();
            $table->string('phone_alt')->nullable();
            $table->enum('relationship', ['ayah', 'ibu', 'wali'])->default('ayah');
            $table->text('address')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('parent_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('parents')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['parent_id', 'student_id']);
        });

        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('plate_number')->unique();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('color')->nullable();
            $table->smallInteger('year')->nullable();
            $table->string('stnk_owner_name')->nullable();
            $table->string('stnk_photo_path')->nullable();
            $table->string('sim_number')->nullable();
            $table->enum('sim_type', ['C', 'C1', 'tidak_ada'])->default('tidak_ada');
            $table->json('requirements')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('vehicle_permits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->string('permit_number')->unique();
            $table->string('qr_token', 32)->unique();
            $table->enum('status', ['draft', 'printed', 'active', 'suspended', 'revoked', 'expired'])->default('draft');
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('printed_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->date('expires_at');
            $table->timestamp('revoked_at')->nullable();
            $table->text('revoked_reason')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('print_count')->default(0);
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_permits');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('parent_student');
        Schema::dropIfExists('parents');
        Schema::dropIfExists('students');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('gates');
        Schema::dropIfExists('academic_years');
    }
};
