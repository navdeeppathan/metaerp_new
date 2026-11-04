<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('code', 64)->nullable();
            $table->string('productCategory', 64)->nullable();
            $table->string('chemicalFormula')->nullable();
            $table->string('unitOfMeasurement', 64)->nullable();
            $table->string('hsCode', 64)->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedBigInteger('version')->default(0);
            $table->timestamps();
        });

        Schema::create('contact_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contact_id');
            $table->unsignedBigInteger('product_id');

            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });

        Schema::create('productcontracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contract_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->double('quantity')->default(0.0);
            $table->double('unitPrice')->default(0.0);
            $table->string('status', 64)->nullable();
            $table->unsignedBigInteger('version')->default(0);
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productcontracts');
        Schema::dropIfExists('contact_products');
        Schema::dropIfExists('products');
    }
};
