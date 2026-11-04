<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebAuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\WebContractController;
use App\Http\Controllers\WebSearchController;
use App\Http\Controllers\WebOfferController;
use App\Http\Controllers\WebCompanyController;
use App\Http\Controllers\WebContactController;
use App\Http\Controllers\WebProductController;
use App\Http\Controllers\WebUserController;
use App\Http\Controllers\WebAccessGroupController;
use App\Http\Controllers\WebProfileController;
use App\Http\Controllers\WebDashboardController;
use App\Http\Controllers\WebSettingsController;
use App\Http\Controllers\WebDetachedNoteController;
use App\Http\Controllers\WebTemplateController;

// --- Guest / Authentication Routes ---
Route::middleware('guest')->group(function () {
    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login'])->name('login.post');
    Route::get('/two-factor-challenge', [WebAuthController::class, 'showTwoFactor'])->name('auth.2fa');
    Route::post('/two-factor-challenge', [WebAuthController::class, 'verifyTwoFactor'])->name('auth.2fa.post');
    Route::get('/sign-up', fn() => redirect()->route('login'))->name('register');
    Route::post('/sign-up', fn() => redirect()->route('login'))->name('register.post');
});

// --- Authenticated Application Routes ---
Route::middleware('auth')->group(function () {
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

    Route::get('/', fn() => redirect()->route('home'));
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // Contracts (Kanban Scrumboard Pipeline)
    Route::get('/contracts', [WebContractController::class, 'board'])->name('contracts.index');
    Route::get('/contracts/models/{boardId?}', [WebContractController::class, 'board'])->name('contracts.board');
    Route::get('/contracts/{id}/modal', [WebContractController::class, 'cardModal'])->name('contracts.modal');
    Route::get('/contracts/{id}', [WebContractController::class, 'show'])->name('contracts.show');
    Route::get('/findContracts/{id}', [WebContractController::class, 'show']);
    Route::post('/contracts', [WebContractController::class, 'store'])->name('contracts.store');
    Route::match(['post', 'put'], '/contracts/{id}/move', [WebContractController::class, 'moveStage'])->name('contracts.move');
    Route::match(['post', 'put'], '/contracts/{id}/move-stage', [WebContractController::class, 'moveStage'])->name('contracts.move-stage');
    Route::match(['post', 'put'], '/contracts/{id}/cancel', [WebContractController::class, 'cancel'])->name('contracts.cancel');
    Route::match(['post', 'put'], '/contracts/{id}/close', [WebContractController::class, 'close'])->name('contracts.close');
    Route::post('/contracts/{id}/comments', [WebContractController::class, 'addComment'])->name('contracts.comments');
    Route::post('/checkitems/{id}/toggle', [WebContractController::class, 'toggleCheckitem'])->name('checkitems.toggle');
    Route::post('/contracts/{id}/generate-doc', [WebContractController::class, 'generateDoc'])->name('contracts.generateDoc');

    // Find Contracts (Search & Advanced Query)
    Route::get('/findContracts', [WebSearchController::class, 'index'])->name('contracts.search');

    // Commercial Offers
    Route::get('/offers', [WebOfferController::class, 'index'])->name('offers.index');
    Route::get('/offers/new', [WebOfferController::class, 'create'])->name('offers.create');
    Route::get('/offers/{id}', [WebOfferController::class, 'show'])->name('offers.show');
    Route::post('/offers', [WebOfferController::class, 'store'])->name('offers.store');
    Route::put('/offers/{id}', [WebOfferController::class, 'update'])->name('offers.update');
    Route::post('/offers/{id}/convert', [WebOfferController::class, 'convertToContract'])->name('offers.convert');
    Route::post('/offers/{id}/status', [WebOfferController::class, 'updateStatus'])->name('offers.status');
    Route::delete('/offers/{id}', [WebOfferController::class, 'destroy'])->name('offers.destroy');

    // Directory & CRM
    Route::get('/companies', [WebCompanyController::class, 'index'])->name('companies.index');
    Route::get('/companies/new', [WebCompanyController::class, 'create'])->name('companies.create');
    Route::get('/companies/{id}', [WebCompanyController::class, 'show'])->name('companies.show');
    Route::post('/companies', [WebCompanyController::class, 'store'])->name('companies.store');
    Route::put('/companies/{id}', [WebCompanyController::class, 'update'])->name('companies.update');
    Route::delete('/companies/{id}', [WebCompanyController::class, 'destroy'])->name('companies.destroy');
    Route::post('/companies/{id}/upload', [WebCompanyController::class, 'uploadMedia'])->name('companies.upload');

    Route::get('/contacts', [WebContactController::class, 'index'])->name('contacts.index');
    Route::get('/contacts/new', [WebContactController::class, 'create'])->name('contacts.create');
    Route::get('/contacts/{id}', [WebContactController::class, 'show'])->name('contacts.show');
    Route::post('/contacts', [WebContactController::class, 'store'])->name('contacts.store');
    Route::put('/contacts/{id}', [WebContactController::class, 'update'])->name('contacts.update');
    Route::delete('/contacts/{id}', [WebContactController::class, 'destroy'])->name('contacts.destroy');

    Route::get('/products', [WebProductController::class, 'index'])->name('products.index');
    Route::get('/products/new', [WebProductController::class, 'create'])->name('products.create');
    Route::get('/products/{id}', [WebProductController::class, 'show'])->name('products.show');
    Route::post('/products', [WebProductController::class, 'store'])->name('products.store');
    Route::put('/products/{id}', [WebProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{id}', [WebProductController::class, 'destroy'])->name('products.destroy');

    // Administration & Access Control
    Route::get('/users', [WebUserController::class, 'index'])->name('users.index');
    Route::get('/users/new', [WebUserController::class, 'create'])->name('users.create');
    Route::get('/users/{id}', [WebUserController::class, 'show'])->name('users.show');
    Route::post('/users', [WebUserController::class, 'store'])->name('users.store');
    Route::put('/users/{id}', [WebUserController::class, 'update'])->name('users.update');
    Route::post('/users/{id}/signature', [WebUserController::class, 'uploadSignature'])->name('users.signature');

    Route::get('/accessgroups', [WebAccessGroupController::class, 'index'])->name('accessgroups.index');
    Route::get('/accessgroups/new', [WebAccessGroupController::class, 'create'])->name('accessgroups.create');
    Route::get('/accessgroups/{id}', [WebAccessGroupController::class, 'show'])->name('accessgroups.show');
    Route::post('/accessgroups', [WebAccessGroupController::class, 'store'])->name('accessgroups.store');
    Route::put('/accessgroups/{id}', [WebAccessGroupController::class, 'update'])->name('accessgroups.update');
    Route::delete('/accessgroups/{id}', [WebAccessGroupController::class, 'destroy'])->name('accessgroups.destroy');

    // Profile & Account
    Route::get('/apps/profile', [WebProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile', [WebProfileController::class, 'index']);
    Route::put('/profile', [WebProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [WebProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/2fa', [WebProfileController::class, 'toggle2fa'])->name('profile.2fa');
    Route::post('/profile/toggle-2fa', [WebProfileController::class, 'toggle2fa'])->name('profile.toggle-2fa');

    // Analytics Dashboard
    Route::get('/dashboards/analytics', [WebDashboardController::class, 'analytics'])->name('dashboards.analytics');
    Route::get('/dashboard', fn() => redirect()->route('dashboards.analytics'))->name('dashboard');

    // Detached Notes
    Route::get('/detached-notes', [WebDetachedNoteController::class, 'index'])->name('detached-notes.index');

    // Templates
    Route::get('/templates', [WebTemplateController::class, 'index'])->name('templates.index');
    Route::get('/templates/{id}', [WebTemplateController::class, 'show'])->name('templates.show');
    Route::put('/templates/{id}', [WebTemplateController::class, 'update'])->name('templates.update');
    Route::post('/templates/{id}', [WebTemplateController::class, 'update']);
    Route::post('/detached-notes', [WebDetachedNoteController::class, 'store'])->name('detached-notes.store');
    Route::delete('/detached-notes/{id}', [WebDetachedNoteController::class, 'destroy'])->name('detached-notes.destroy');

    // System Settings Lookups
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
});
