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
    Route::post('/accessgroups', [WebAccessGroupController::class, 'store'])->name('accessgroups.store');
    Route::put('/accessgroups/{id}', [WebAccessGroupController::class, 'update'])->name('accessgroups.update');

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

    // System Settings Lookups
    Route::get('/countries', [WebSettingsController::class, 'countries'])->name('settings.countries');
    Route::get('/settings/countries', [WebSettingsController::class, 'countries']);
    Route::get('/currencies', [WebSettingsController::class, 'currencies'])->name('settings.currencies');
    Route::get('/settings/currencies', [WebSettingsController::class, 'currencies']);
    Route::get('/departments', [WebSettingsController::class, 'departments'])->name('settings.departments');
    Route::get('/settings/departments', [WebSettingsController::class, 'departments']);
    Route::get('/packTypes', [WebSettingsController::class, 'packTypes'])->name('settings.packTypes');
    Route::get('/settings/pack-types', [WebSettingsController::class, 'packTypes']);
    Route::get('/paymentTypes', [WebSettingsController::class, 'paymentTypes'])->name('settings.paymentTypes');
    Route::get('/settings/payment-types', [WebSettingsController::class, 'paymentTypes']);
    Route::get('/paymentTermsTypes', [WebSettingsController::class, 'paymentTerms'])->name('settings.paymentTerms');
    Route::get('/settings/payment-terms', [WebSettingsController::class, 'paymentTerms']);
});
