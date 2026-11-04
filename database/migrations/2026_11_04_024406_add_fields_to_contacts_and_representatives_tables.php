<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('eoriNumber')->nullable();
            $table->string('website')->nullable();
            $table->text('additionalInformation')->nullable();
            $table->string('types')->nullable();
            $table->string('template_language')->nullable();
        });

        Schema::table('representatives', function (Blueprint $table) {
            $table->string('job_title')->nullable();
            $table->string('phone2')->nullable();
            $table->string('mobile')->nullable();
            $table->string('department')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn(['eoriNumber', 'website', 'additionalInformation', 'types', 'template_language']);
        });

        Schema::table('representatives', function (Blueprint $table) {
            $table->dropColumn(['job_title', 'phone2', 'mobile', 'department']);
        });
    }
};
