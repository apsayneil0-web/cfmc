<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('username', 50)->unique();
            $table->string('email', 100)->unique();
            $table->string('password');

            $table->string('Status', 20)->default('Active');
            $table->boolean('isLoggedIn')->default(false);

            $table->unsignedBigInteger('RoleID')->default(3);

            $table->string('PhoneNumber', 20)->nullable();
            $table->string('OTP', 6)->nullable();
            $table->dateTime('OTPExpiry')->nullable();

            $table->boolean('FirstTimeLogin')->default(true);
            $table->integer('FailedLoginAttempts')->default(0);
            $table->dateTime('LockedUntil')->nullable();

            $table->timestamps();

            $table->foreign('RoleID')
                  ->references('RoleID')
                  ->on('role')
                  ->onDelete('restrict');

            $table->index('Status', 'idx_Status');
            $table->index('isLoggedIn', 'idx_isLoggedIn');
            $table->index('RoleID', 'idx_RoleID');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};