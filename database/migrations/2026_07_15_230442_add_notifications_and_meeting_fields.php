<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->time('time')->nullable()->after('announcement_date');
            $table->string('location')->nullable()->after('time');
        });

        // Remap existing rows onto the new purpose taxonomy before narrowing the enum.
        DB::table('announcements')->where('purpose', 'Event')->update(['purpose' => 'Meeting']);
        DB::table('announcements')->where('purpose', 'Announcement')->update(['purpose' => 'Information']);

        DB::statement("ALTER TABLE announcements MODIFY purpose ENUM('Information', 'Meeting', 'Reminder', 'Resolution') NOT NULL DEFAULT 'Information'");

        // Materialized, per-recipient notification rows (fan-out on publish),
        // replacing the read-tracking-only announcement_reads table: this lets
        // the bell render a precomputed title/message/type/is_read directly
        // instead of recomputing visibility for every announcement on every request.
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('announcement_id')->constrained('announcements')->onDelete('cascade');
            $table->string('title');
            $table->string('message', 160);
            $table->enum('type', ['announcement', 'meeting', 'reminder', 'resolution'])->default('announcement');
            $table->boolean('is_read')->default(false);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::dropIfExists('announcement_reads');
    }

    public function down(): void
    {
        Schema::create('announcement_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained('announcements')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('read_at');
            $table->unique(['announcement_id', 'user_id']);
        });

        Schema::dropIfExists('notifications');

        DB::statement("ALTER TABLE announcements MODIFY purpose ENUM('Information', 'Reminder', 'Event', 'Announcement') NOT NULL DEFAULT 'Information'");

        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(['time', 'location']);
        });
    }
};
