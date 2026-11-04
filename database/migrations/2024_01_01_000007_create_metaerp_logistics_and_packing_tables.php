<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packinglists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contract_id');
            $table->string('vesselName')->nullable();
            $table->string('shippingLine')->nullable();
            $table->string('blNumber', 64)->nullable();
            $table->string('portOfLoading')->nullable();
            $table->string('portOfDischarge')->nullable();
            $table->string('status', 64)->default('ACTIVE');
            $table->unsignedBigInteger('version')->default(0);
            $table->timestamps();

            $table->foreign('contract_id')->references('id')->on('contracts')->onDelete('cascade');
        });

        Schema::create('packs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('packing_list_id');
            $table->unsignedBigInteger('pack_type_id')->nullable();
            $table->string('containerNumber', 64)->nullable();
            $table->string('sealNumber', 64)->nullable();
            $table->double('grossWeight')->default(0.0);
            $table->double('netWeight')->default(0.0);
            $table->double('tareWeight')->default(0.0);
            $table->integer('quantity')->default(1);
            $table->unsignedBigInteger('version')->default(0);
            $table->timestamps();

            $table->foreign('packing_list_id')->references('id')->on('packinglists')->onDelete('cascade');
            $table->foreign('pack_type_id')->references('id')->on('packtype')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packs');
        Schema::dropIfExists('packinglists');
    }
};
