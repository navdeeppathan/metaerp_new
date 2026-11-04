<?php

$content = file_get_contents(__DIR__ . '/../routes/web.php');
$content = str_replace("\r\n", "\n", $content);

$search = "    Route::get('/departments', [WebSettingsController::class, 'departments'])->name('settings.departments');\n    Route::get('/settings/departments', [WebSettingsController::class, 'departments']);\n    Route::get('/packTypes', [WebSettingsController::class, 'packTypes'])->name('settings.packTypes');\n    Route::get('/settings/pack-types', [WebSettingsController::class, 'packTypes']);";

$replace = <<<PHP
    // Departments (Screenshots 1 & 2)
    Route::get('/departments', [WebSettingsController::class, 'departments'])->name('settings.departments');
    Route::get('/settings/departments', [WebSettingsController::class, 'departments']);
    Route::get('/departments/new', [WebSettingsController::class, 'createDepartment'])->name('settings.departments.create');
    Route::get('/departments/{id}', [WebSettingsController::class, 'showDepartment'])->name('settings.departments.show');
    Route::post('/departments', [WebSettingsController::class, 'storeDepartment'])->name('settings.departments.store');
    Route::put('/departments/{id}', [WebSettingsController::class, 'updateDepartment'])->name('settings.departments.update');
    Route::delete('/departments/{id}', [WebSettingsController::class, 'destroyDepartment'])->name('settings.departments.destroy');

    // PackTypes (Screenshots 3, 4, 5)
    Route::get('/packTypes', [WebSettingsController::class, 'packTypes'])->name('settings.packTypes');
    Route::get('/settings/pack-types', [WebSettingsController::class, 'packTypes']);
    Route::get('/packTypes/new', [WebSettingsController::class, 'createPackType'])->name('settings.packTypes.create');
    Route::get('/packTypes/{id}', [WebSettingsController::class, 'showPackType'])->name('settings.packTypes.show');
    Route::post('/packTypes', [WebSettingsController::class, 'storePackType'])->name('settings.packTypes.store');
    Route::put('/packTypes/{id}', [WebSettingsController::class, 'updatePackType'])->name('settings.packTypes.update');
    Route::delete('/packTypes/{id}', [WebSettingsController::class, 'destroyPackType'])->name('settings.packTypes.destroy');
PHP;

if (strpos($content, $search) !== false) {
    file_put_contents(__DIR__ . '/../routes/web.php', str_replace($search, $replace, $content));
    echo "Departments and PackTypes routes updated successfully!\n";
} else {
    echo "Search pattern not found in routes/web.php\n";
}
