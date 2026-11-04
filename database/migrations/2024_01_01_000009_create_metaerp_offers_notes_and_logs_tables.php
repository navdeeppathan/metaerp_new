<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer', function (Blueprint $table) {
            $table->id();
            $table->string('orderCode', 100)->nullable();
            $table->string('type', 64); // BUY / SELL
            $table->unsignedBigInteger('meta_company_id')->nullable();
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->double('quantity')->default(0.0);
            $table->double('unitPrice')->default(0.0);
            $table->double('total')->default(0.0);
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->string('status', 64)->default('OPEN');
            $table->dateTime('validUntil')->nullable();
            $table->unsignedBigInteger('version')->default(0);
            $table->timestamps();

            $table->foreign('meta_company_id')->references('id')->on('companies')->onDelete('set null');
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('set null');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
            $table->foreign('currency_id')->references('id')->on('currency')->onDelete('set null');
        });

        Schema::create('detachednote', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 64); // CREDIT / DEBIT
            $table->string('number', 64)->nullable();
            $table->dateTime('date')->nullable();
            $table->unsignedBigInteger('meta_company_id')->nullable();
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->double('total')->default(0.0);
            $table->unsignedBigInteger('version')->default(0);
            $table->timestamps();

            $table->foreign('meta_company_id')->references('id')->on('companies')->onDelete('set null');
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('set null');
            $table->foreign('currency_id')->references('id')->on('currency')->onDelete('set null');
        });

        Schema::create('detachednotedetail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('detached_note_id');
            $table->string('description');
            $table->double('quantity')->default(0.0);
            $table->double('unitPrice')->default(0.0);
            $table->double('total')->default(0.0);
            $table->unsignedBigInteger('version')->default(0);
            $table->timestamps();

            $table->foreign('detached_note_id')->references('id')->on('detachednote')->onDelete('cascade');
        });

        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->string('module', 64);
            $table->unsignedBigInteger('dataId');
            $table->dateTime('dateTime');
            $table->unsignedBigInteger('userId')->nullable();
            $table->string('action', 64);
            $table->longText('history')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logs');
        Schema::dropIfExists('detachednotedetail');
        Schema::dropIfExists('detachednote');
        Schema::dropIfExists('offer');
    }
};
