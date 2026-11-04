<?php

$content = file_get_contents(__DIR__ . '/../routes/web.php');
$content = str_replace("\r\n", "\n", $content);

$search = "    // Credit/Debit Types\n    Route::get('/credit-debit-types', [WebSettingsController::class, 'creditDebitTypes'])->name('settings.creditDebitTypes');\n    Route::get('/settings/credit-debit-types', [WebSettingsController::class, 'creditDebitTypes']);\n    Route::get('/currencies', [WebSettingsController::class, 'currencies'])->name('settings.currencies');\n    Route::get('/settings/currencies', [WebSettingsController::class, 'currencies']);";

$replace = <<<PHP
    // Credit/Debit Types (Screenshots 1 & 2)
    Route::get('/credit-debit-types', [WebSettingsController::class, 'creditDebitTypes'])->name('settings.creditDebitTypes');
    Route::get('/settings/credit-debit-types', [WebSettingsController::class, 'creditDebitTypes']);
    Route::get('/credit-debit-types/new', [WebSettingsController::class, 'createCreditDebitType'])->name('settings.creditDebitTypes.create');
    Route::get('/credit-debit-types/{id}', [WebSettingsController::class, 'showCreditDebitType'])->name('settings.creditDebitTypes.show');
    Route::post('/credit-debit-types', [WebSettingsController::class, 'storeCreditDebitType'])->name('settings.creditDebitTypes.store');
    Route::put('/credit-debit-types/{id}', [WebSettingsController::class, 'updateCreditDebitType'])->name('settings.creditDebitTypes.update');
    Route::delete('/credit-debit-types/{id}', [WebSettingsController::class, 'destroyCreditDebitType'])->name('settings.creditDebitTypes.destroy');

    // Currencies (Screenshots 3 & 4)
    Route::get('/currencies', [WebSettingsController::class, 'currencies'])->name('settings.currencies');
    Route::get('/settings/currencies', [WebSettingsController::class, 'currencies']);
    Route::get('/currencies/new', [WebSettingsController::class, 'createCurrency'])->name('settings.currencies.create');
    Route::get('/currencies/{id}', [WebSettingsController::class, 'showCurrency'])->name('settings.currencies.show');
    Route::post('/currencies', [WebSettingsController::class, 'storeCurrency'])->name('settings.currencies.store');
    Route::put('/currencies/{id}', [WebSettingsController::class, 'updateCurrency'])->name('settings.currencies.update');
    Route::delete('/currencies/{id}', [WebSettingsController::class, 'destroyCurrency'])->name('settings.currencies.destroy');
PHP;

if (strpos($content, $search) !== false) {
    file_put_contents(__DIR__ . '/../routes/web.php', str_replace($search, $replace, $content));
    echo "Routes updated successfully!\n";
} else {
    echo "Search pattern not found in routes/web.php\n";
}
