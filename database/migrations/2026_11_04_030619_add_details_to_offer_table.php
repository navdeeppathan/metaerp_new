<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offer', function (Blueprint $table) {
            $table->string('subject')->nullable();
            $table->string('code')->nullable();
            $table->string('contract_code')->nullable();
            $table->dateTime('offer_date')->nullable();
            $table->dateTime('accepted_at')->nullable();
            $table->text('description')->nullable();
            $table->text('internal_notes')->nullable();
            $table->string('delivery_plan')->nullable();
            $table->string('quotational_period')->nullable();
            $table->string('weights')->nullable();
            $table->string('payment_description')->nullable();
            $table->string('terms')->nullable();
            $table->text('extra_information')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('offer', function (Blueprint $table) {
            $table->dropColumn([
                'subject', 'code', 'contract_code', 'offer_date', 'accepted_at',
                'description', 'internal_notes', 'delivery_plan', 'quotational_period',
                'weights', 'payment_description', 'terms', 'extra_information'
            ]);
        });
    }
};
