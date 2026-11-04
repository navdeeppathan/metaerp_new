<?php

$content = file_get_contents(__DIR__ . '/../routes/web.php');
$content = str_replace("\r\n", "\n", $content);

$search = "    Route::get('/paymentTypes', [WebSettingsController::class, 'paymentTypes'])->name('settings.paymentTypes');\n    Route::get('/settings/payment-types', [WebSettingsController::class, 'paymentTypes']);\n    Route::get('/paymentTermsTypes', [WebSettingsController::class, 'paymentTerms'])->name('settings.paymentTerms');\n    Route::get('/settings/payment-terms', [WebSettingsController::class, 'paymentTerms']);";

$replace = <<<PHP
    // PaymentTypes (Screenshots 1, 2, 3)
    Route::get('/paymentTypes', [WebSettingsController::class, 'paymentTypes'])->name('settings.paymentTypes');
    Route::get('/settings/payment-types', [WebSettingsController::class, 'paymentTypes']);
    Route::get('/paymentTypes/new', [WebSettingsController::class, 'createPaymentType'])->name('settings.paymentTypes.create');
    Route::get('/paymentTypes/{id}', [WebSettingsController::class, 'showPaymentType'])->name('settings.paymentTypes.show');
    Route::post('/paymentTypes', [WebSettingsController::class, 'storePaymentType'])->name('settings.paymentTypes.store');
    Route::put('/paymentTypes/{id}', [WebSettingsController::class, 'updatePaymentType'])->name('settings.paymentTypes.update');
    Route::delete('/paymentTypes/{id}', [WebSettingsController::class, 'destroyPaymentType'])->name('settings.paymentTypes.destroy');

    // PaymentTermsTypes (Screenshots 4 & 5)
    Route::get('/paymentTermsTypes', [WebSettingsController::class, 'paymentTerms'])->name('settings.paymentTerms');
    Route::get('/settings/payment-terms', [WebSettingsController::class, 'paymentTerms']);
    Route::get('/paymentTermsTypes/new', [WebSettingsController::class, 'createPaymentTerm'])->name('settings.paymentTerms.create');
    Route::get('/paymentTermsTypes/{id}', [WebSettingsController::class, 'showPaymentTerm'])->name('settings.paymentTerms.show');
    Route::post('/paymentTermsTypes', [WebSettingsController::class, 'storePaymentTerm'])->name('settings.paymentTerms.store');
    Route::put('/paymentTermsTypes/{id}', [WebSettingsController::class, 'updatePaymentTerm'])->name('settings.paymentTerms.update');
    Route::delete('/paymentTermsTypes/{id}', [WebSettingsController::class, 'destroyPaymentTerm'])->name('settings.paymentTerms.destroy');
PHP;

if (strpos($content, $search) !== false) {
    file_put_contents(__DIR__ . '/../routes/web.php', str_replace($search, $replace, $content));
    echo "Payment routes updated successfully!\n";
} else {
    echo "Search pattern not found in routes/web.php\n";
}
