<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\AccessGroup;
use App\Models\Access;
use App\Models\Permission;
use App\Models\Country;
use App\Models\Language;
use App\Models\Currency;
use App\Models\Type;
use App\Models\Department;
use App\Models\PackType;
use App\Models\PaymentType;
use App\Models\PaymentTermsType;
use App\Models\CreditDebitType;
use App\Models\Company;
use App\Models\Address;
use App\Models\BankAccount;
use App\Models\Representative;
use App\Models\Contact;
use App\Models\Product;
use App\Models\ModelWorkflow;
use App\Models\Stage;
use App\Models\Action;
use App\Models\StageAction;
use App\Models\Contract;
use App\Models\ContractOrderCodeSequence;
use App\Models\Deal;
use App\Models\DealProduct;
use App\Models\PackingList;
use App\Models\Pack;
use App\Models\Checklist;
use App\Models\Checkitem;
use App\Models\Comment;
use App\Models\Template;
use App\Models\Offer;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Currencies
        $usd = Currency::create(['description' => 'USD', 'symbol' => '$', 'rate' => 1.25]);
        $gbp = Currency::create(['description' => 'GBP', 'symbol' => '£', 'rate' => 1.00]);
        $eur = Currency::create(['description' => 'EUR', 'symbol' => '€', 'rate' => 1.15]);

        // 2. Pack Types
        $packBag = PackType::create(['description' => 'Bags']);
        $packDrum = PackType::create(['description' => 'Drums']);
        $packPallet = PackType::create(['description' => 'Pallets']);
        $packBulk = PackType::create(['description' => 'Bulk']);

        // 3. Payment Terms Types
        $ptCad = PaymentTermsType::create(['description' => 'CAD (Cash Against Documents)']);
        $ptLc = PaymentTermsType::create(['description' => 'Letter of Credit (LC)']);
        $ptNet30 = PaymentTermsType::create(['description' => 'Net 30 Days']);
        $ptAdvance = PaymentTermsType::create(['description' => '100% TT in Advance']);

        // 4. Payment Types
        $payWire = PaymentType::create(['description' => 'Wire Transfer (Swift)']);
        $payTt = PaymentType::create(['description' => 'Telegraphic Transfer (TT)']);
        $payCheque = PaymentType::create(['description' => 'Bank Cheque']);

        // 5. Credit Debit Types
        CreditDebitType::create(['description' => 'Price Adjustment']);
        CreditDebitType::create(['description' => 'Quality Discrepancy']);
        CreditDebitType::create(['description' => 'Weight Difference']);

        // 6. Countries
        $uk = Country::create([
            'name' => 'United Kingdom', 'alpha2' => 'GB', 'alpha3' => 'GBR',
            'region' => 'Europe', 'currencyName' => 'Pound Sterling', 'currencyCode' => 'GBP',
            'currencySymbol' => '£', 'vat' => 20.0, 'active' => true,
        ]);
        $brazil = Country::create([
            'name' => 'Brazil', 'alpha2' => 'BR', 'alpha3' => 'BRA',
            'region' => 'South America', 'currencyName' => 'Brazilian Real', 'currencyCode' => 'BRL',
            'currencySymbol' => 'R$', 'vat' => 10.0, 'active' => true,
        ]);
        $usa = Country::create([
            'name' => 'United States', 'alpha2' => 'US', 'alpha3' => 'USA',
            'region' => 'North America', 'currencyName' => 'US Dollar', 'currencyCode' => 'USD',
            'currencySymbol' => '$', 'vat' => 0.0, 'active' => true,
        ]);
        $germany = Country::create([
            'name' => 'Germany', 'alpha2' => 'DE', 'alpha3' => 'DEU',
            'region' => 'Europe', 'currencyName' => 'Euro', 'currencyCode' => 'EUR',
            'currencySymbol' => '€', 'vat' => 19.0, 'active' => true,
        ]);

        // 7. Languages
        $en = Language::create(['name' => 'English', 'code' => 'en']);
        $es = Language::create(['name' => 'Spanish', 'code' => 'es']);
        $pt = Language::create(['name' => 'Portuguese', 'code' => 'pt']);

        // 8. Departments
        Department::create(['name' => 'Trading & Procurement']);
        Department::create(['name' => 'Logistics & Shipping']);
        Department::create(['name' => 'Finance & Accounting']);
        Department::create(['name' => 'Quality Control']);

        // 9. Contact Types
        $typeBuyer = Type::create(['description' => 'Buyer']);
        $typeSeller = Type::create(['description' => 'Seller']);
        $typeBroker = Type::create(['description' => 'Broker']);
        $typeTransporter = Type::create(['description' => 'Transporter']);

        // 10. Products
        $copper = Product::create([
            'name' => 'Copper Cathodes Grade A',
            'description' => 'Electrolytic Copper Cathodes 99.99% purity',
            'code' => 'CU-CAT-A',
            'productCategory' => 'NON_FERROUS',
            'chemicalFormula' => 'Cu 99.99%',
            'unitOfMeasurement' => 'MT',
            'hsCode' => '74031100',
            'active' => true,
        ]);
        $aluminium = Product::create([
            'name' => 'Aluminium Ingots 99.7%',
            'description' => 'Primary Aluminium Ingots grade P1020A',
            'code' => 'AL-ING-997',
            'productCategory' => 'NON_FERROUS',
            'chemicalFormula' => 'Al 99.7%',
            'unitOfMeasurement' => 'MT',
            'hsCode' => '76011000',
            'active' => true,
        ]);
        $steelScrap = Product::create([
            'name' => 'Heavy Melting Scrap (HMS 1&2)',
            'description' => 'ISRI 200-206 specification ferrous scrap steel',
            'code' => 'FE-HMS-8020',
            'productCategory' => 'FERROUS',
            'chemicalFormula' => 'Fe > 92%',
            'unitOfMeasurement' => 'MT',
            'hsCode' => '72044900',
            'active' => true,
        ]);

        // 11. Company Address & Company
        $metaAddress = Address::create([
            'addressName' => 'Headquarters',
            'addressLine1' => '25 Bank Street',
            'addressLine2' => 'Canary Wharf',
            'city' => 'London',
            'state' => 'Greater London',
            'postalCode' => 'E14 5JP',
            'country_id' => $uk->id,
            'active' => true,
        ]);

        $metaCompany = Company::create([
            'name' => 'Metaenterprices Ltd',
            'code' => 'ME',
            'countryId' => $uk->id,
            'companyNumber' => '07890123',
            'vatNumber' => 'GB987654321',
            'phoneNumber' => '+44 20 7946 0199',
            'email' => 'operations@metaenterprices.co.uk',
            'webSite' => 'https://metaenterprices.co.uk',
            'eoriNumber' => 'GB987654321000',
            'address_id' => $metaAddress->id,
            'active' => true,
        ]);

        $metaBank = BankAccount::create([
            'company_id' => $metaCompany->id,
            'name' => 'Barclays Bank UK',
            'sortCode' => '20-00-00',
            'accountNumber' => '98765432',
            'iban' => 'GB29BARC20000098765432',
            'swift' => 'BARCGB22',
        ]);

        $metaRep = Representative::create([
            'company_id' => $metaCompany->id,
            'name' => 'James Harrison',
            'email' => 'james.harrison@metaenterprices.co.uk',
            'phone' => '+44 7700 900123',
            'type' => 'SALES',
        ]);

        // 12. Contact (Supplier / Buyer)
        $csnContact = Contact::create([
            'name' => 'CSN Siderúrgica Nacional',
            'initials' => 'CSN',
            'vat' => 'BR-33042730000104',
            'registration' => 'REG-109283',
            'codeMeta' => 'BR00001',
            'countryId' => $brazil->id,
            'currency_id' => $usd->id,
            'language_id' => $pt->id,
            'active' => true,
        ]);
        $csnContact->types()->sync([$typeBuyer->id, $typeSeller->id]);
        $csnContact->products()->sync([$copper->id, $steelScrap->id]);

        $csnAddress = Address::create([
            'contact_id' => $csnContact->id,
            'addressName' => 'Usina Presidente Vargas',
            'addressLine1' => 'Rodovia Lúcio Meira, s/n',
            'city' => 'Volta Redonda',
            'state' => 'Rio de Janeiro',
            'postalCode' => '27260-390',
            'country_id' => $brazil->id,
            'active' => true,
        ]);

        $csnRep = Representative::create([
            'contact_id' => $csnContact->id,
            'name' => 'Carlos Alberto Silva',
            'email' => 'carlos.silva@csn.com.br',
            'phone' => '+55 24 3344 5500',
            'type' => 'SALES',
        ]);

        $csnBank = BankAccount::create([
            'contact_id' => $csnContact->id,
            'name' => 'Banco Itaú BBA',
            'sortCode' => '0341',
            'accountNumber' => '1029384',
            'iban' => 'BR54034100000010293840001',
            'swift' => 'ITAUBRSP',
        ]);

        // 13. Access Groups & Permissions
        $adminGroup = AccessGroup::create(['name' => 'Super Administrator', 'active' => true]);
        $modules = ['DASHBOARD', 'USERS', 'PRODUCT', 'COMPANY', 'CONTACT', 'OFFER', 'CONTRACT', 'ACCESS_CONTROL', 'SYSTEM_SETTINGS'];
        $actions = ['CREATE', 'READ', 'UPDATE', 'DELETE'];

        foreach ($modules as $mod) {
            foreach ($actions as $act) {
                Access::create([
                    'accessgroup_id' => $adminGroup->id,
                    'module' => $mod,
                    'accessType' => $act,
                    'active' => true,
                ]);
            }
        }

        // 14. Admin User
        $adminUser = User::create([
            'name' => 'Administrator',
            'displayName' => 'System Admin',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'occupation' => 'Chief Executive',
            'role' => 'ROLE_ADMIN',
            'active' => true,
            'secret' => 'JBSWY3DPEHPK3PXP', // Base32 test secret
            'enable2fa' => false,
        ]);

        Permission::create([
            'user_id' => $adminUser->id,
            'company_id' => $metaCompany->id,
            'accessgroup_id' => $adminGroup->id,
        ]);

        // 15. Model Workflows & Stages
        $pipelineModel = ModelWorkflow::create([
            'name' => 'Standard Commodity Trading Pipeline',
            'description' => 'End-to-end trading pipeline from quotation to shipment and settlement',
            'active' => true,
        ]);

        // Secondary board model to support /contracts/models/2
        $secondaryModel = ModelWorkflow::create([
            'name' => 'Fast-Track Spot Trading Pipeline',
            'description' => 'Spot market deals pipeline',
            'active' => true,
        ]);

        $stagesList = [
            ['name' => 'Draft', 'desc' => 'New trade contract draft', 'priority' => 1, 'stageType' => 'DRAFT'],
            ['name' => 'Under Review', 'desc' => 'Trade terms and KYC verification', 'priority' => 2, 'stageType' => 'REVIEW'],
            ['name' => 'Approved', 'desc' => 'Counterparty signed and executed', 'priority' => 3, 'stageType' => 'APPROVED'],
            ['name' => 'Logistics', 'desc' => 'Vessel booking and container packing', 'priority' => 4, 'stageType' => 'LOGISTICS'],
            ['name' => 'Completed', 'desc' => 'Discharged and payments settled', 'priority' => 5, 'stageType' => 'COMPLETED'],
        ];

        $createdStages = [];
        foreach ($stagesList as $s) {
            $createdStages[] = Stage::create([
                'model_id' => $secondaryModel->id,
                'name' => $s['name'],
                'description' => $s['desc'],
                'priority' => $s['priority'],
                'stageType' => $s['stageType'],
                'active' => true,
            ]);
            // Also replicate for primary model
            Stage::create([
                'model_id' => $pipelineModel->id,
                'name' => $s['name'],
                'description' => $s['desc'],
                'priority' => $s['priority'],
                'stageType' => $s['stageType'],
                'active' => true,
            ]);
        }

        // 16. Document Templates
        Template::create([
            'name' => 'Standard Purchase Order',
            'type' => 'PURCHASE_ORDER',
            'language_id' => $en->id,
            'keys' => '{meta_company_name},{meta_company_number},{contract_buyer_name},{contract_order_code},{contract_date},{contract_product_table},{contract_total_amount}',
            'html' => '<html><head><style>body{font-family:Arial,sans-serif;margin:40px;color:#333;}.header{border-bottom:2px solid #2563eb;padding-bottom:10px;}.title{font-size:24px;font-weight:bold;color:#1e3a8a;}</style></head><body><div class="header"><div class="title">PURCHASE ORDER</div><div>Company: {meta_company_name} (Reg: {meta_company_number})</div><div>Order Code: <strong>{contract_order_code}</strong> | Date: {contract_date}</div></div><p>To Supplier: <strong>{contract_buyer_name}</strong></p><div>{contract_product_table}</div><p style="font-size:18px;font-weight:bold;">Total Amount: {contract_total_amount}</p></body></html>',
        ]);

        Template::create([
            'name' => 'Standard Sales Contract',
            'type' => 'CONTRACT_OF_SALES',
            'language_id' => $en->id,
            'keys' => '{meta_company_name},{contract_buyer_name},{contract_order_code},{contract_date},{contract_product_table},{contract_total_amount}',
            'html' => '<html><head><style>body{font-family:Arial,sans-serif;margin:40px;color:#333;}.header{border-bottom:2px solid #059669;padding-bottom:10px;}.title{font-size:24px;font-weight:bold;color:#065f46;}</style></head><body><div class="header"><div class="title">CONTRACT OF SALES</div><div>Seller: {meta_company_name}</div><div>Contract Code: <strong>{contract_order_code}</strong> | Date: {contract_date}</div></div><p>Buyer: <strong>{contract_buyer_name}</strong></p><div>{contract_product_table}</div><p style="font-size:18px;font-weight:bold;">Total Value: {contract_total_amount}</p></body></html>',
        ]);

        Template::create([
            'name' => 'Commercial Invoice',
            'type' => 'COMMERCIAL_INVOICE',
            'language_id' => $en->id,
            'keys' => '{meta_company_name},{contract_buyer_name},{contract_order_code},{contract_date},{contract_product_table},{contract_total_amount}',
            'html' => '<html><head><style>body{font-family:Arial,sans-serif;margin:40px;color:#333;}</style></head><body><h2>COMMERCIAL INVOICE</h2><p>Invoice for Contract: {contract_order_code}</p><div>{contract_product_table}</div><h3>Total Due: {contract_total_amount}</h3></body></html>',
        ]);

        Template::create([
            'name' => 'Packing List',
            'type' => 'PACKING_LIST',
            'language_id' => $en->id,
            'keys' => '{meta_company_name},{contract_order_code},{packing_list_vessel},{packing_list_bl},{packing_list_table}',
            'html' => '<html><head><style>body{font-family:Arial,sans-serif;margin:40px;color:#333;}</style></head><body><h2>CONSIGNMENT PACKING LIST</h2><p>Contract: {contract_order_code} | Vessel: {packing_list_vessel} | B/L: {packing_list_bl}</p><div>{packing_list_table}</div></body></html>',
        ]);

        // 17. Contract Order Code Sequence
        ContractOrderCodeSequence::create(['year' => 2024, 'lastId' => 100]);
        ContractOrderCodeSequence::create(['year' => 2025, 'lastId' => 200]);
        ContractOrderCodeSequence::create(['year' => (int)date('Y'), 'lastId' => 0]);

        // 18. Sample Purchase Deal & Contract
        $purchaseDeal = Deal::create([
            'meta_company_id' => $metaCompany->id,
            'contact_id' => $csnContact->id,
            'contact_representative_id' => $csnRep->id,
            'meta_representative_id' => $metaRep->id,
            'address_id' => $csnAddress->id,
            'destination_address_id' => $metaAddress->id,
            'currency_id' => $usd->id,
            'payment_type_id' => $payWire->id,
            'payment_terms_type_id' => $ptCad->id,
            'bank_account_id' => $metaBank->id,
            'dollarConvertionRate' => 1.0,
            'total' => 450000.00,
            'netTotal' => 450000.00,
            'dealPaymentStatus' => 'PENDING',
        ]);

        DealProduct::create([
            'deal_id' => $purchaseDeal->id,
            'product_id' => $copper->id,
            'pack_type_id' => $packPallet->id,
            'currency_id' => $usd->id,
            'quantity' => 50.0,
            'unitPrice' => 9000.00,
            'pricingType' => 'FIXED',
            'vat' => 0.0,
            'total' => 450000.00,
            'status' => 'PENDING',
        ]);

        $sampleContract = Contract::create([
            'orderCode' => 'MENON_FERROUS26-001',
            'date' => now(),
            'type' => 'BUY',
            'stage_id' => $createdStages[1]->id, // Under Review
            'purchase_id' => $purchaseDeal->id,
            'contractStatus' => 'ACTIVE',
            'active' => true,
            'loadingDate' => now()->addDays(14),
            'deliveryTerms' => 'CIF Rotterdam Port',
            'suppliesNote' => 'High purity electrolytic grade cathodes with mill test certificates.',
            'customerPurchaseOrder' => 'PO-CSN-2026-99',
            'modeOfPayment' => 'CAD through Barclays London',
            'qualityCheck' => 'SGS Inspection at loading port',
            'packing' => 'Strapped bundles on wooden pallets, plastic wrapped',
            'remarks' => 'Priority shipment for Q1 2026.',
        ]);

        // Packing List for Sample Contract
        $pl = PackingList::create([
            'contract_id' => $sampleContract->id,
            'vesselName' => 'MSC LORETTO',
            'shippingLine' => 'Mediterranean Shipping Company',
            'blNumber' => 'MSCLO88776655',
            'portOfLoading' => 'Port of Santos, Brazil',
            'portOfDischarge' => 'Port of Rotterdam, Netherlands',
            'status' => 'ACTIVE',
        ]);

        Pack::create([
            'packing_list_id' => $pl->id,
            'pack_type_id' => $packPallet->id,
            'containerNumber' => 'MSCU1234567',
            'sealNumber' => 'SL-998877',
            'grossWeight' => 25200.0,
            'netWeight' => 25000.0,
            'tareWeight' => 200.0,
            'quantity' => 25,
        ]);

        Pack::create([
            'packing_list_id' => $pl->id,
            'pack_type_id' => $packPallet->id,
            'containerNumber' => 'MSCU7654321',
            'sealNumber' => 'SL-998878',
            'grossWeight' => 25200.0,
            'netWeight' => 25000.0,
            'tareWeight' => 200.0,
            'quantity' => 25,
        ]);

        // Checklists & Comments
        $cl = Checklist::create(['contract_id' => $sampleContract->id, 'name' => 'KYC & Trade Compliance']);
        Checkitem::create(['checklist_id' => $cl->id, 'name' => 'Sanctions & AML Screening', 'checked' => true]);
        Checkitem::create(['checklist_id' => $cl->id, 'name' => 'Credit Insurance Approval', 'checked' => true]);
        Checkitem::create(['checklist_id' => $cl->id, 'name' => 'Proforma Invoice Signed by Supplier', 'checked' => false]);

        Comment::create([
            'contract_id' => $sampleContract->id,
            'user_id' => $adminUser->id,
            'message' => 'Contract created successfully. Verified vessel schedule with MSC line.',
            'created_at' => now(),
        ]);

        // 19. Sample Commercial Offer
        Offer::create([
            'orderCode' => 'OFF-2026-001',
            'type' => 'SELL',
            'meta_company_id' => $metaCompany->id,
            'contact_id' => $csnContact->id,
            'product_id' => $aluminium->id,
            'quantity' => 100.0,
            'unitPrice' => 2450.00,
            'total' => 245000.00,
            'currency_id' => $usd->id,
            'status' => 'OPEN',
            'validUntil' => now()->addDays(7),
        ]);
    }
}
