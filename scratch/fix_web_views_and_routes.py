import os

# 1. Update routes/web.php
web_routes_path = r"c:\Users\navde\Downloads\metaerp\routes\web.php"
with open(web_routes_path, "r", encoding="utf-8") as f:
    web_routes = f.read()

# Make sure contracts/{id}/move-stage has {id}
web_routes = web_routes.replace(
    "Route::match(['post', 'put'], '/contracts/{contractId}/move', [WebContractController::class, 'moveStage'])->name('contracts.move');",
    "Route::match(['post', 'put'], '/contracts/{id}/move', [WebContractController::class, 'moveStage'])->name('contracts.move');"
)
web_routes = web_routes.replace(
    "Route::match(['post', 'put'], '/contracts/{contractId}/move-stage', [WebContractController::class, 'moveStage'])->name('contracts.move-stage');",
    "Route::match(['post', 'put'], '/contracts/{id}/move-stage', [WebContractController::class, 'moveStage'])->name('contracts.move-stage');"
)

# Add offers.convert
if "offers.convert" not in web_routes:
    web_routes = web_routes.replace(
        "Route::post('/offers', [WebOfferController::class, 'store'])->name('offers.store');",
        "Route::post('/offers', [WebOfferController::class, 'store'])->name('offers.store');\n    Route::post('/offers/{id}/convert', [WebOfferController::class, 'convertToContract'])->name('offers.convert');"
    )

# Add profile.toggle-2fa
if "profile.toggle-2fa" not in web_routes:
    web_routes = web_routes.replace(
        "Route::post('/profile/2fa', [WebProfileController::class, 'toggle2fa'])->name('profile.2fa');",
        "Route::post('/profile/2fa', [WebProfileController::class, 'toggle2fa'])->name('profile.2fa');\n    Route::post('/profile/toggle-2fa', [WebProfileController::class, 'toggle2fa'])->name('profile.toggle-2fa');"
    )

# Add /settings/* route aliases
settings_block = """    // System Settings Lookups
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
    Route::get('/settings/payment-terms', [WebSettingsController::class, 'paymentTerms']);"""

if "Route::get('/settings/countries'" not in web_routes:
    orig_settings = """    // System Settings Lookups
    Route::get('/countries', [WebSettingsController::class, 'countries'])->name('settings.countries');
    Route::get('/currencies', [WebSettingsController::class, 'currencies'])->name('settings.currencies');
    Route::get('/departments', [WebSettingsController::class, 'departments'])->name('settings.departments');
    Route::get('/packTypes', [WebSettingsController::class, 'packTypes'])->name('settings.packTypes');
    Route::get('/paymentTypes', [WebSettingsController::class, 'paymentTypes'])->name('settings.paymentTypes');
    Route::get('/paymentTermsTypes', [WebSettingsController::class, 'paymentTerms'])->name('settings.paymentTerms');"""
    web_routes = web_routes.replace(orig_settings, settings_block)

with open(web_routes_path, "w", encoding="utf-8") as f:
    f.write(web_routes)
print("Updated routes/web.php")

# 2. Update WebContactController.php to pass $companies
contact_ctrl_path = r"c:\Users\navde\Downloads\metaerp\app\Http\Controllers\WebContactController.php"
with open(contact_ctrl_path, "r", encoding="utf-8") as f:
    c_ctrl = f.read()

if "use App\Models\Company;" not in c_ctrl:
    c_ctrl = c_ctrl.replace("use App\Models\Contact;", "use App\Models\Contact;\nuse App\Models\Company;")

if "$companies = Company::where('active', true)->orderBy('name')->get();" not in c_ctrl:
    c_ctrl = c_ctrl.replace(
        "$contacts = $query->orderBy('name')->paginate(20);",
        "$contacts = $query->orderBy('name')->paginate(20);\n        $companies = Company::where('active', true)->orderBy('name')->get();"
    )
    c_ctrl = c_ctrl.replace(
        "compact('contacts', 'countries', 'types', 'products', 'currencies', 'languages')",
        "compact('contacts', 'companies', 'countries', 'types', 'products', 'currencies', 'languages')"
    )

with open(contact_ctrl_path, "w", encoding="utf-8") as f:
    f.write(c_ctrl)
print("Updated WebContactController.php")

# 3. Update WebUserController.php to pass $groups
user_ctrl_path = r"c:\Users\navde\Downloads\metaerp\app\Http\Controllers\WebUserController.php"
with open(user_ctrl_path, "r", encoding="utf-8") as f:
    u_ctrl = f.read()

if "$groups = $accessGroups;" not in u_ctrl:
    u_ctrl = u_ctrl.replace(
        "$companies = Company::where('active', true)->get();",
        "$companies = Company::where('active', true)->get();\n        $groups = $accessGroups;"
    )
    u_ctrl = u_ctrl.replace(
        "compact('users', 'accessGroups', 'companies')",
        "compact('users', 'accessGroups', 'companies', 'groups')"
    )

with open(user_ctrl_path, "w", encoding="utf-8") as f:
    f.write(u_ctrl)
print("Updated WebUserController.php")

# 4. Update WebOfferController.php to add convertToContract
offer_ctrl_path = r"c:\Users\navde\Downloads\metaerp\app\Http\Controllers\WebOfferController.php"
with open(offer_ctrl_path, "r", encoding="utf-8") as f:
    o_ctrl = f.read()

if "function convertToContract" not in o_ctrl:
    convert_method = """    public function convertToContract($id)
    {
        $offer = Offer::findOrFail($id);
        $offer->status = 'CONVERTED';
        $offer->save();

        return redirect()->route('contracts.board')->with('success', "Offer {$offer->orderCode} converted to contract!");
    }
}"""
    o_ctrl = o_ctrl.rstrip()
    if o_ctrl.endswith("}"):
        o_ctrl = o_ctrl[:-1] + "\n" + convert_method + "\n"

with open(offer_ctrl_path, "w", encoding="utf-8") as f:
    f.write(o_ctrl)
print("Updated WebOfferController.php")

# 5. Fix companies/index.blade.php hasPages check
company_view_path = r"c:\Users\navde\Downloads\metaerp\resources\views\companies\index.blade.php"
with open(company_view_path, "r", encoding="utf-8") as f:
    cv = f.read()

cv = cv.replace("@if($companies->hasPages())", "@if(method_exists($companies, 'hasPages') && $companies->hasPages())")
with open(company_view_path, "w", encoding="utf-8") as f:
    f.write(cv)
print("Updated companies/index.blade.php")
