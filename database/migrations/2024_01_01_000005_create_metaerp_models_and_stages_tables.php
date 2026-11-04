<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description', 500)->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedBigInteger('version')->default(0);
            $table->timestamps();
        });

        Schema::create('stages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->string('name');
            $table->string('description', 500)->nullable();
            $table->integer('priority')->default(0);
            $table->string('stageType', 64)->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedBigInteger('version')->default(0);
            $table->timestamps();

            $table->foreign('model_id')->references('id')->on('models')->onDelete('cascade');
        });

        Schema::create('actions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('action', 64);
            $table->unsignedBigInteger('version')->default(0);
            $table->timestamps();
        });

        Schema::create('stageactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stage_id');
            $table->unsignedBigInteger('action_id');
            $table->string('triggerType', 64);
            $table->integer('priority')->default(0);
            $table->string('contractTypesNotAllowed', 255)->nullable();
            $table->unsignedBigInteger('version')->default(0);
            $table->timestamps();

            $table->foreign('stage_id')->references('id')->on('stages')->onDelete('cascade');
            $table->foreign('action_id')->references('id')->on('actions')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stageactions');
        Schema::dropIfExists('actions');
        Schema::dropIfExists('stages');
        Schema::dropIfExists('models');
    }
};
