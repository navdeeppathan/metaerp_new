<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type', 64);
            $table->unsignedBigInteger('language_id')->nullable();
            $table->longText('html');
            $table->text('keys')->nullable();
            $table->unsignedBigInteger('version')->default(0);
            $table->timestamps();

            $table->foreign('language_id')->references('id')->on('languages')->onDelete('set null');
        });

        Schema::create('generatedfiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contract_id');
            $table->unsignedBigInteger('template_id')->nullable();
            $table->string('fileName');
            $table->longText('html');
            $table->string('fileType', 64)->default('HTML');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('version')->default(0);
            $table->timestamps();

            $table->foreign('contract_id')->references('id')->on('contracts')->onDelete('cascade');
            $table->foreign('template_id')->references('id')->on('templates')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('uploadedfiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contract_id');
            $table->string('fileName');
            $table->string('filePath', 500);
            $table->unsignedBigInteger('fileSize')->default(0);
            $table->string('contentType', 100)->nullable();
            $table->timestamps();

            $table->foreign('contract_id')->references('id')->on('contracts')->onDelete('cascade');
        });

        Schema::create('archive', function (Blueprint $table) {
            $table->id();
            $table->longText('file');
            $table->string('fileName');
            $table->timestamps();
        });

        Schema::create('checklists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contract_id');
            $table->string('name');
            $table->unsignedBigInteger('version')->default(0);
            $table->timestamps();

            $table->foreign('contract_id')->references('id')->on('contracts')->onDelete('cascade');
        });

        Schema::create('checkitems', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('checklist_id');
            $table->string('name');
            $table->boolean('checked')->default(false);
            $table->unsignedBigInteger('version')->default(0);
            $table->timestamps();

            $table->foreign('checklist_id')->references('id')->on('checklists')->onDelete('cascade');
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contract_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('message');
            $table->timestamps();

            $table->foreign('contract_id')->references('id')->on('contracts')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
        Schema::dropIfExists('checkitems');
        Schema::dropIfExists('checklists');
        Schema::dropIfExists('archive');
        Schema::dropIfExists('uploadedfiles');
        Schema::dropIfExists('generatedfiles');
        Schema::dropIfExists('templates');
    }
};
