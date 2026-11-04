<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('alpha2', 2)->nullable();
            $table->string('alpha3', 3)->nullable();
            $table->string('region')->nullable();
            $table->string('subRegion')->nullable();
            $table->string('intermediateRegion')->nullable();
            $table->string('currencyName')->nullable();
            $table->string('currencyCode', 10)->nullable();
            $table->string('currencySymbol', 10)->nullable();
            $table->double('vat')->default(0.0);
            $table->boolean('active')->default(true);
            $table->unsignedBigInteger('version')->default(0);
            $table->timestamps();
        });

        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 10);
            $table->unsignedBigInteger('version')->default(0);
            $table->timestamps();
        });

        Schema::create('currency', function (Blueprint $table) {
            $table->id();
            $table->string('description', 20);
            $table->string('symbol', 10)->nullable();
            $table->double('rate')->default(1.0);
            $table->unsignedBigInteger('version')->default(0);
            $table->timestamps();
        });

        Schema::create('types', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->unsignedBigInteger('version')->default(0);
            $table->timestamps();
        });

        Schema::create('department', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('version')->default(0);
            $table->timestamps();
        });

        Schema::create('packtype', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->unsignedBigInteger('version')->default(0);
            $table->timestamps();
        });

        Schema::create('paymenttype', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->unsignedBigInteger('version')->default(0);
            $table->timestamps();
        });

        Schema::create('paymenttermstype', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->unsignedBigInteger('version')->default(0);
            $table->timestamps();
        });

        Schema::create('creditdebittype', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->unsignedBigInteger('version')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('creditdebittype');
        Schema::dropIfExists('paymenttermstype');
        Schema::dropIfExists('paymenttype');
        Schema::dropIfExists('packtype');
        Schema::dropIfExists('department');
        Schema::dropIfExists('types');
        Schema::dropIfExists('currency');
        Schema::dropIfExists('languages');
        Schema::dropIfExists('countries');
    }
};
