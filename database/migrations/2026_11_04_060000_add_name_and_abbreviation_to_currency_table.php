<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('currency', function (Blueprint $table) {
            if (!Schema::hasColumn('currency', 'name')) {
                $table->string('name')->nullable()->after('description');
            }
            if (!Schema::hasColumn('currency', 'abbreviation')) {
                $table->string('abbreviation', 20)->nullable()->after('name');
            }
            if (!Schema::hasColumn('currency', 'htmlEncodeSymbol')) {
                $table->string('htmlEncodeSymbol', 50)->nullable()->after('symbol');
            }
        });
    }

    public function down(): void
    {
        Schema::table('currency', function (Blueprint $table) {
            if (Schema::hasColumn('currency', 'name')) {
                $table->dropColumn(['name', 'abbreviation', 'htmlEncodeSymbol']);
            }
        });
    }
};
