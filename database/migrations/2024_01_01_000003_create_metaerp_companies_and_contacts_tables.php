<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->string('addressName')->nullable();
            $table->string('addressLine1')->nullable();
            $table->string('addressLine2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postalCode')->nullable();
            $table->string('postalBox')->nullable();
            $table->boolean('active')->nullable()->default(true);
            $table->string('forGoogleMapsSearch', 500)->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->unsignedBigInteger('version')->default(0);
            $table->timestamps();

            $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');
        });

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 64);
            $table->unsignedBigInteger('countryId')->nullable();
            $table->string('logoUrl', 500)->nullable();
            $table->string('companyNumber', 64)->nullable();
            $table->string('vatNumber', 64)->nullable();
            $table->string('phoneNumber', 64)->nullable();
            $table->string('faxNumber', 64)->nullable();
            $table->string('email')->nullable();
            $table->string('webSite')->nullable();
            $table->string('footer', 500)->nullable();
            $table->string('eoriNumber', 64)->nullable();
            $table->unsignedBigInteger('address_id')->nullable();
            $table->unsignedBigInteger('archiveLogoId')->nullable();
            $table->unsignedBigInteger('archiveFooterId')->nullable();
            $table->unsignedBigInteger('archiveStampId')->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedBigInteger('version')->default(0);
            $table->timestamps();

            $table->foreign('address_id')->references('id')->on('addresses')->onDelete('set null');
        });

        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('initials', 64)->nullable();
            $table->unsignedBigInteger('companyId')->nullable();
            $table->string('vat', 64)->nullable();
            $table->string('registration', 64)->nullable();
            $table->string('codeMeta', 64)->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->unsignedBigInteger('countryId')->nullable();
            $table->unsignedBigInteger('language_id')->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedBigInteger('version')->default(0);
            $table->timestamps();

            $table->foreign('currency_id')->references('id')->on('currency')->onDelete('set null');
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('set null');
        });

        Schema::create('bankaccounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->string('name')->nullable();
            $table->string('sortCode', 64)->nullable();
            $table->string('accountNumber', 64)->nullable();
            $table->string('iban', 128)->nullable();
            $table->string('swift', 64)->nullable();
            $table->unsignedBigInteger('version')->default(0);
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
        });

        Schema::create('representatives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->unsignedBigInteger('address_id')->nullable();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 64)->nullable();
            $table->string('type', 64)->nullable();
            $table->unsignedBigInteger('version')->default(0);
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('accessgroup_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('accessgroup_id')->references('id')->on('accessgroups')->onDelete('cascade');
        });

        Schema::create('contact_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contact_id');
            $table->unsignedBigInteger('type_id');

            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
            $table->foreign('type_id')->references('id')->on('types')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_types');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('representatives');
        Schema::dropIfExists('bankaccounts');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('companies');
        Schema::dropIfExists('addresses');
    }
};
