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
            $table->string('name');
            $table->string('displayName')->nullable();
            $table->string('photoUrl')->nullable();
            $table->longText('signatureFile')->nullable();
            $table->string('signature_file_name')->nullable();
            $table->string('occupation')->nullable();
            $table->string('username')->unique();
            $table->string('password');
            $table->boolean('active')->default(true);
            $table->string('role')->default('ROLE_USER');
            $table->string('secret')->nullable();
            $table->boolean('enable2fa')->default(false);
            $table->unsignedBigInteger('archiveSignatureId')->nullable();
            $table->unsignedBigInteger('version')->default(0);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('accessgroups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('active')->default(true);
            $table->unsignedBigInteger('version')->default(0);
            $table->timestamps();
        });

        Schema::create('access', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('accessgroup_id')->nullable();
            $table->string('module', 64);
            $table->string('accessType', 64);
            $table->boolean('active')->default(true);
            $table->unsignedBigInteger('version')->default(0);
            $table->timestamps();

            $table->foreign('accessgroup_id')->references('id')->on('accessgroups')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access');
        Schema::dropIfExists('accessgroups');
        Schema::dropIfExists('users');
    }
};
