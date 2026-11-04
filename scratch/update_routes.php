<?php

$content = file_get_contents(__DIR__ . '/../routes/web.php');
// Normalize line endings to \n
$content = str_replace("\r\n", "\n", $content);

$search = "    Route::get('/countries', [WebSettingsController::class, 'countries'])->name('settings.countries');\n    Route::get('/settings/countries', [WebSettingsController::class, 'countries']);";

$replace = <<<PHP
    // Countries (Screenshots 1 & 2)
    Route::get('/countries', [WebSettingsController::class, 'countries'])->name('settings.countries');
    Route::get('/settings/countries', [WebSettingsController::class, 'countries']);
    Route::get('/countries/new', [WebSettingsController::class, 'createCountry'])->name('settings.countries.create');
    Route::get('/countries/{id}', [WebSettingsController::class, 'showCountry'])->name('settings.countries.show');
    Route::post('/countries', [WebSettingsController::class, 'storeCountry'])->name('settings.countries.store');
    Route::put('/countries/{id}', [WebSettingsController::class, 'updateCountry'])->name('settings.countries.update');
    Route::delete('/countries/{id}', [WebSettingsController::class, 'destroyCountry'])->name('settings.countries.destroy');

    // Company Types (Screenshots 3 & 4)
    Route::get('/company-types', [WebSettingsController::class, 'companyTypes'])->name('settings.companyTypes');
    Route::get('/settings/company-types', [WebSettingsController::class, 'companyTypes']);
    Route::get('/company-types/new', [WebSettingsController::class, 'createCompanyType'])->name('settings.companyTypes.create');
    Route::get('/company-types/{id}', [WebSettingsController::class, 'showCompanyType'])->name('settings.companyTypes.show');
    Route::post('/company-types', [WebSettingsController::class, 'storeCompanyType'])->name('settings.companyTypes.store');
    Route::put('/company-types/{id}', [WebSettingsController::class, 'updateCompanyType'])->name('settings.companyTypes.update');
    Route::delete('/company-types/{id}', [WebSettingsController::class, 'destroyCompanyType'])->name('settings.companyTypes.destroy');

    // Credit/Debit Types
    Route::get('/credit-debit-types', [WebSettingsController::class, 'creditDebitTypes'])->name('settings.creditDebitTypes');
    Route::get('/settings/credit-debit-types', [WebSettingsController::class, 'creditDebitTypes']);
PHP;

if (strpos($content, $search) !== false) {
    file_put_contents(__DIR__ . '/../routes/web.php', str_replace($search, $replace, $content));
    echo "Routes updated successfully!\n";
} else {
    echo "Search pattern not found in routes/web.php\n";
}
