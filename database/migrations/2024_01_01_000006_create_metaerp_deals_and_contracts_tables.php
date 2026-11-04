<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contractOrderCodeSequence', function (Blueprint $table) {
            $table->id();
            $table->integer('year')->unique();
            $table->integer('lastId')->default(0);
            $table->timestamps();
        });

        Schema::create('deal', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meta_company_id')->nullable();
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->unsignedBigInteger('contact_representative_id')->nullable();
            $table->unsignedBigInteger('meta_representative_id')->nullable();
            $table->unsignedBigInteger('address_id')->nullable();
            $table->unsignedBigInteger('destination_address_id')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->unsignedBigInteger('payment_type_id')->nullable();
            $table->unsignedBigInteger('payment_terms_type_id')->nullable();
            $table->unsignedBigInteger('bank_account_id')->nullable();
            $table->double('dollarConvertionRate')->default(1.0);
            $table->double('total')->default(0.0);
            $table->double('netTotal')->default(0.0);
            $table->string('dealPaymentStatus', 64)->nullable();
            $table->unsignedBigInteger('version')->default(0);
            $table->timestamps();

            $table->foreign('meta_company_id')->references('id')->on('companies')->onDelete('set null');
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('set null');
            $table->foreign('contact_representative_id')->references('id')->on('representatives')->onDelete('set null');
            $table->foreign('meta_representative_id')->references('id')->on('representatives')->onDelete('set null');
            $table->foreign('address_id')->references('id')->on('addresses')->onDelete('set null');
            $table->foreign('destination_address_id')->references('id')->on('addresses')->onDelete('set null');
            $table->foreign('currency_id')->references('id')->on('currency')->onDelete('set null');
            $table->foreign('payment_type_id')->references('id')->on('paymenttype')->onDelete('set null');
            $table->foreign('payment_terms_type_id')->references('id')->on('paymenttermstype')->onDelete('set null');
            $table->foreign('bank_account_id')->references('id')->on('bankaccounts')->onDelete('set null');
        });

        Schema::create('dealproduct', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('deal_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('pack_type_id')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->double('quantity')->default(0.0);
            $table->double('unitPrice')->default(0.0);
            $table->string('pricingType', 64)->nullable();
            $table->double('vat')->default(0.0);
            $table->double('total')->default(0.0);
            $table->unsignedBigInteger('associatedContractId')->nullable();
            $table->unsignedBigInteger('associatedProductId')->nullable();
            $table->string('status', 64)->nullable();
            $table->unsignedBigInteger('version')->default(0);
            $table->timestamps();

            $table->foreign('deal_id')->references('id')->on('deal')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
            $table->foreign('pack_type_id')->references('id')->on('packtype')->onDelete('set null');
            $table->foreign('currency_id')->references('id')->on('currency')->onDelete('set null');
        });

        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('orderCode', 100)->nullable()->unique();
            $table->dateTime('date')->nullable();
            $table->string('type', 64); // BUY / SELL
            $table->unsignedBigInteger('stage_id')->nullable();
            $table->unsignedBigInteger('purchase_id')->nullable();
            $table->unsignedBigInteger('sale_id')->nullable();
            $table->string('contractStatus', 64)->default('ACTIVE');
            $table->boolean('active')->default(true);
            $table->dateTime('loadingDate')->nullable();
            $table->string('deliveryTerms')->nullable();
            $table->text('suppliesNote')->nullable();
            $table->string('customerPurchaseOrder')->nullable();
            $table->string('modeOfPayment')->nullable();
            $table->string('qualityCheck')->nullable();
            $table->string('packing')->nullable();
            $table->text('remarks')->nullable();
            $table->text('memberIds')->nullable();
            $table->unsignedBigInteger('version')->default(0);
            $table->timestamps();

            $table->foreign('stage_id')->references('id')->on('stages')->onDelete('set null');
            $table->foreign('purchase_id')->references('id')->on('deal')->onDelete('set null');
            $table->foreign('sale_id')->references('id')->on('deal')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
        Schema::dropIfExists('dealproduct');
        Schema::dropIfExists('deal');
        Schema::dropIfExists('contractOrderCodeSequence');
    }
};
