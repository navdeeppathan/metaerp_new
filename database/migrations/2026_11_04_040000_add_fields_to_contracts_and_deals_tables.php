<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            if (!Schema::hasColumn('contracts', 'weightTolerance')) {
                $table->string('weightTolerance')->nullable()->after('type');
            }
            if (!Schema::hasColumn('contracts', 'orderDescription')) {
                $table->text('orderDescription')->nullable()->after('weightTolerance');
            }
            if (!Schema::hasColumn('contracts', 'finishDate')) {
                $table->dateTime('finishDate')->nullable()->after('date');
            }
            if (!Schema::hasColumn('contracts', 'cancellationDate')) {
                $table->dateTime('cancellationDate')->nullable()->after('finishDate');
            }
        });

        Schema::table('deal', function (Blueprint $table) {
            if (!Schema::hasColumn('deal', 'paymentDate')) {
                $table->date('paymentDate')->nullable();
            }
            if (!Schema::hasColumn('deal', 'incoterms')) {
                $table->string('incoterms')->nullable();
            }
            if (!Schema::hasColumn('deal', 'customerOrderNumber')) {
                $table->string('customerOrderNumber')->nullable();
            }
            if (!Schema::hasColumn('deal', 'paymentTerms')) {
                $table->string('paymentTerms')->nullable();
            }
            if (!Schema::hasColumn('deal', 'advancedPayment')) {
                $table->string('advancedPayment')->nullable();
            }
            if (!Schema::hasColumn('deal', 'notes')) {
                $table->text('notes')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['weightTolerance', 'orderDescription', 'finishDate', 'cancellationDate']);
        });
        Schema::table('deal', function (Blueprint $table) {
            $table->dropColumn(['paymentDate', 'incoterms', 'customerOrderNumber', 'paymentTerms', 'advancedPayment', 'notes']);
        });
    }
};
