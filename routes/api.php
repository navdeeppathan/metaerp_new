<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AccessGroupController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ContractController;
use App\Http\Controllers\Api\OfferController;
use App\Http\Controllers\Api\ModelController;
use App\Http\Controllers\Api\StageController;
use App\Http\Controllers\Api\PackingListController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\GeneratedFileController;
use App\Http\Controllers\Api\TemplateController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\PackTypeController;
use App\Http\Controllers\Api\PaymentTypeController;
use App\Http\Controllers\Api\PaymentTermsTypeController;
use App\Http\Controllers\Api\CreditDebitTypeController;
use App\Http\Controllers\Api\TypeController;
use App\Http\Controllers\Api\LanguageController;
use App\Http\Controllers\Api\MetalPriceController;
use App\Http\Controllers\Api\MetabaseEmbedController;
use App\Http\Controllers\WebDashboardController;
use App\Http\Controllers\Api\TOTPController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\LogController;
use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\FilesController;
use App\Http\Controllers\Api\ContractReactivationController;
use App\Http\Controllers\Api\DetachedNoteController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\BankAccountController;

// --- Public Routes ---
Route::post('/login', [AuthController::class, 'login']);
Route::get('/login', [AuthController::class, 'validateToken']);
Route::post('/sign-up', [AuthController::class, 'register']);
Route::get('/api/version', [ApplicationController::class, 'getVersion']);
Route::get('/version', [ApplicationController::class, 'getVersion']);
Route::get('/files/{filename}', [FilesController::class, 'show'])->where('filename', '.*');
Route::get('/users/download/signature/{userId}', [UserController::class, 'downloadSignature']);

// --- Users ---
Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::post('/users', [UserController::class, 'store']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);
Route::post('/users/upload/signature/{userId}', [UserController::class, 'uploadSignature']);

// --- Access Groups ---
Route::get('/accessgroup', [AccessGroupController::class, 'index']);
Route::get('/accessgroup/{id}', [AccessGroupController::class, 'show']);
Route::post('/accessgroup', [AccessGroupController::class, 'store']);
Route::put('/accessgroup/{id}', [AccessGroupController::class, 'update']);
Route::delete('/accessgroup/{id}', [AccessGroupController::class, 'destroy']);

// --- Companies ---
Route::get('/companies', [CompanyController::class, 'index']);
Route::get('/companies/{id}', [CompanyController::class, 'show']);
Route::post('/companies', [CompanyController::class, 'store']);
Route::put('/companies/{id}', [CompanyController::class, 'update']);
Route::delete('/companies/{id}', [CompanyController::class, 'destroy']);
Route::post('/companies/upload/logo/{companyId}', [CompanyController::class, 'uploadLogo']);
Route::post('/companies/upload/footer/{companyId}', [CompanyController::class, 'uploadFooter']);
Route::post('/companies/upload/stamp/{companyId}', [CompanyController::class, 'uploadStamp']);

// --- Contacts ---
Route::get('/contacts', [ContactController::class, 'index']);
Route::get('/contacts/{id}', [ContactController::class, 'show']);
Route::post('/contacts', [ContactController::class, 'store']);
Route::put('/contacts/{id}', [ContactController::class, 'update']);
Route::delete('/contacts/{id}', [ContactController::class, 'destroy']);

// --- Products ---
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::post('/products', [ProductController::class, 'store']);
Route::put('/products/{id}', [ProductController::class, 'update']);
Route::delete('/products/{id}', [ProductController::class, 'destroy']);

// --- Contracts ---
// Specific routes first to prevent /{id} wildcard capturing
Route::get('/contracts/contractsForAssociation/{productId}', [ContractController::class, 'listContractsForAssociation']);
Route::get('/contracts/sale-contracts-with-pending-products/{contractId}/{productId}', [ContractController::class, 'listPendingContracts']);
Route::get('/contracts/status/{status}', [ContractController::class, 'listByStatus']);
Route::post('/contracts/listByCriteria', [ContractController::class, 'listByCriteria']);
Route::post('/contracts/{contractId}/move/{stageId}', [ContractController::class, 'move']);
Route::post('/contracts/{dealProductId}/associateWith/{purchaseContractId}', [ContractController::class, 'associateContract']);
Route::get('/contracts/{saleContractId}/associatedContracts', [ContractController::class, 'listAssociatedContractsIds']);
Route::get('/contracts/{saleContractId}/associatedContractsWithOrderCode', [ContractController::class, 'listAssociatedContractsOrderCode']);
Route::get('/contracts/{purchaseContractId}/listProductsRemainingBalance', [ContractController::class, 'listProductsRemainingBalance']);
Route::put('/contracts/cancel/{id}', [ContractController::class, 'cancel']);
Route::put('/contracts/close/{id}', [ContractController::class, 'close']);

// File management routes
Route::get('/contracts/{contractId}/files', [ContractController::class, 'listFilesForContract']);
Route::post('/contracts/upload/{contractId}/files', [ContractController::class, 'uploadContractFiles']);
Route::delete('/contracts/delete/{contractId}/files/{fileId}', [ContractController::class, 'deleteFileFromContract']);
Route::get('/contracts/download/{contractId}/files/{fileId}', [ContractController::class, 'downloadFileFromDisk']);

// Standard CRUD
Route::get('/contracts', [ContractController::class, 'index']);
Route::get('/contracts/{id}', [ContractController::class, 'show']);
Route::post('/contracts', [ContractController::class, 'store']);
Route::put('/contracts/{id}', [ContractController::class, 'update']);
Route::delete('/contracts/{id}', [ContractController::class, 'destroy']);

// --- Offers ---
Route::get('/offers', [OfferController::class, 'index']);
Route::get('/offers/{id}', [OfferController::class, 'show']);
Route::post('/offers', [OfferController::class, 'store']);
Route::put('/offers/{id}', [OfferController::class, 'update']);
Route::delete('/offers/{id}', [OfferController::class, 'destroy']);

// --- Models & Stages ---
Route::get('/models', [ModelController::class, 'index']);
Route::get('/models/{id}', [ModelController::class, 'show']);
Route::get('/models/withpermissions/{id}', [ModelController::class, 'withPermissions']);
Route::post('/models', [ModelController::class, 'store']);
Route::put('/models/{id}', [ModelController::class, 'update']);
Route::delete('/models/{id}', [ModelController::class, 'destroy']);

Route::get('/stages', [StageController::class, 'index']);
Route::get('/stages/{id}', [StageController::class, 'show']);
Route::post('/stages', [StageController::class, 'store']);
Route::put('/stages/{id}', [StageController::class, 'update']);
Route::delete('/stages/{id}', [StageController::class, 'destroy']);

// --- Packing Lists ---
Route::get('/packing-lists', [PackingListController::class, 'index']);
Route::get('/packing-lists/{id}', [PackingListController::class, 'show']);
Route::post('/packing-lists', [PackingListController::class, 'store']);
Route::put('/packing-lists/{id}', [PackingListController::class, 'update']);
Route::delete('/packing-lists/{id}', [PackingListController::class, 'destroy']);
Route::post('/packing-lists/{id}/packs', [PackingListController::class, 'addPack']);
Route::delete('/packing-lists/{id}/packs/{packId}', [PackingListController::class, 'removePack']);

// --- Documents & Templates ---
Route::post('/documents/regenerate', [DocumentController::class, 'regenerate']);
Route::post('/documents/packing-list-related-documents', [DocumentController::class, 'generatePackingListRelatedDocuments']);

Route::get('/generated-files', [GeneratedFileController::class, 'index']);
Route::get('/generated-files/{id}', [GeneratedFileController::class, 'show']);
Route::get('/generated-files/{id}/html', [GeneratedFileController::class, 'getHtml']);
Route::put('/generated-files/{id}', [GeneratedFileController::class, 'update']);
Route::delete('/generated-files/{id}', [GeneratedFileController::class, 'destroy']);

Route::get('/templates', [TemplateController::class, 'index']);
Route::get('/templates/{id}', [TemplateController::class, 'show']);
Route::get('/templates/type/{type}/language/{lang}', [TemplateController::class, 'findByTypeAndLanguage']);
Route::post('/templates', [TemplateController::class, 'store']);
Route::put('/templates/{id}', [TemplateController::class, 'update']);
Route::delete('/templates/{id}', [TemplateController::class, 'destroy']);

// --- System Lookups ---
Route::apiResource('countries', CountryController::class);
Route::apiResource('currencies', CurrencyController::class);
Route::apiResource('departments', DepartmentController::class);
Route::apiResource('packTypes', PackTypeController::class);
Route::apiResource('paymentTypes', PaymentTypeController::class);
Route::apiResource('paymentTermsTypes', PaymentTermsTypeController::class);
Route::apiResource('creditDebitTypes', CreditDebitTypeController::class);
Route::apiResource('types', TypeController::class);
Route::apiResource('languages', LanguageController::class);

// --- Utilities & Integrations ---
Route::get('/metal-price', [MetalPriceController::class, 'index']);
Route::get('/metabase/embed', [MetabaseEmbedController::class, 'embed']);
Route::get('/dashboards/analytics/widgets', [WebDashboardController::class, 'apiWidgets']);
Route::get('/api/dashboards/analytics/widgets', [WebDashboardController::class, 'apiWidgets']);
Route::get('/totp/{id}', [TOTPController::class, 'getQrCode']);
Route::post('/totp/{id}', [TOTPController::class, 'update']);
Route::get('/search/{text}', [SearchController::class, 'search']);
Route::get('/logs/{module}/{dataId}', [LogController::class, 'listByModuleAndDataId']);
Route::post('/contract-reactivation/{id}', [ContractReactivationController::class, 'reactivate']);
Route::apiResource('detached-notes', DetachedNoteController::class);
Route::apiResource('addresses', AddressController::class);
Route::apiResource('accounts', BankAccountController::class);
