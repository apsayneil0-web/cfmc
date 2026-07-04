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
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->after('id');
            $table->string('status')->default('active')->after('password');
            $table->boolean('isloggedin')->default(false)->after('status');
            $table->unsignedBigInteger('roleID')->default(3)->after('isloggedin');
            $table->string('Phonenumber')->nullable()->after('roleID');
            $table->string('OTP')->nullable()->after('Phonenumber');
            $table->timestamp('OTPexpriry')->nullable()->after('OTP');
            $table->boolean('firstTimelogin')->default(true)->after('OTPexpriry');
            $table->integer('FailedLoginAttemps')->default(0)->after('firstTimelogin');
            $table->timestamp('LockedUntil')->nullable()->after('FailedLoginAttemps');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username',
                'status',
                'isloggedin',
                'roleID',
                'Phonenumber',
                'OTP',
                'OTPexpriry',
                'firstTimelogin',
                'FailedLoginAttemps',
                'LockedUntil'
            ]);
        });
    }
};
