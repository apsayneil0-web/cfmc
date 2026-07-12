<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE schedule_requests DROP FOREIGN KEY schedule_requests_user_id_foreign");
        DB::statement("ALTER TABLE schedule_requests MODIFY user_id BIGINT UNSIGNED NULL");
        DB::statement("ALTER TABLE schedule_requests ADD CONSTRAINT schedule_requests_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE");

        DB::statement("ALTER TABLE schedule_requests MODIFY status ENUM('pending', 'approved', 'denied', 'completed') NOT NULL DEFAULT 'pending'");

        Schema::table('schedule_requests', function (Blueprint $table) {
            $table->string('farmer_name')->nullable()->after('user_id');
            $table->enum('member_type', ['member', 'non-member'])->default('member')->after('farmer_name');
            $table->time('start_time')->nullable()->after('scheduled_date');
            $table->time('end_time')->nullable()->after('start_time');
            $table->string('location')->nullable()->after('end_time');
            $table->boolean('is_reschedule')->default(false)->after('status');
            $table->foreignId('original_schedule_id')->nullable()->after('is_reschedule')
                ->constrained('schedule_requests')->nullOnDelete();
            $table->text('denial_reason')->nullable()->after('original_schedule_id');
            $table->text('remarks')->nullable()->after('denial_reason');
            $table->decimal('harvest_yield', 8, 2)->nullable()->after('remarks');
            $table->timestamp('archived_at')->nullable()->after('harvest_yield');
        });
    }

    public function down(): void
    {
        Schema::table('schedule_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('original_schedule_id');
            $table->dropColumn([
                'farmer_name',
                'member_type',
                'start_time',
                'end_time',
                'location',
                'is_reschedule',
                'denial_reason',
                'remarks',
                'harvest_yield',
                'archived_at',
            ]);
        });

        DB::statement("ALTER TABLE schedule_requests MODIFY status ENUM('pending', 'approved', 'denied') NOT NULL DEFAULT 'pending'");

        DB::statement("ALTER TABLE schedule_requests DROP FOREIGN KEY schedule_requests_user_id_foreign");
        DB::statement("ALTER TABLE schedule_requests MODIFY user_id BIGINT UNSIGNED NOT NULL");
        DB::statement("ALTER TABLE schedule_requests ADD CONSTRAINT schedule_requests_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE");
    }
};
