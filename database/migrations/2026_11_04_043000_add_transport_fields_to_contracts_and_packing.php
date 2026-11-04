<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            if (!Schema::hasColumn('contracts', 'priceAgreed')) {
                $table->string('priceAgreed')->nullable();
            }
            if (!Schema::hasColumn('contracts', 'transportNotes')) {
                $table->text('transportNotes')->nullable();
            }
            if (!Schema::hasColumn('contracts', 'specialNotes')) {
                $table->text('specialNotes')->nullable();
            }
        });

        Schema::table('packinglists', function (Blueprint $table) {
            if (!Schema::hasColumn('packinglists', 'packingListCode')) {
                $table->string('packingListCode')->nullable();
            }
            if (!Schema::hasColumn('packinglists', 'deliveryNote')) {
                $table->string('deliveryNote')->nullable();
            }
            if (!Schema::hasColumn('packinglists', 'date')) {
                $table->date('date')->nullable();
            }
        });

        Schema::table('packs', function (Blueprint $table) {
            if (!Schema::hasColumn('packs', 'product_id')) {
                $table->unsignedBigInteger('product_id')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['priceAgreed', 'transportNotes', 'specialNotes']);
        });
        Schema::table('packinglists', function (Blueprint $table) {
            $table->dropColumn(['packingListCode', 'deliveryNote', 'date']);
        });
        Schema::table('packs', function (Blueprint $table) {
            $table->dropColumn(['product_id']);
        });
    }
};
