<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Template;
use App\Models\GeneratedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class DocumentTemplateService
{
    /**
     * Standard document types for each stage.
     */
    public const STAGE_DOCUMENTS = [
        'SALES_ORDER' => [
            'PROFORMA_INVOICE',
            'DELIVERY_NOTE',
            'CONTRACT_OF_SALES',
            'SPECIMEN_CONTRACT',
            'EXPORT_LIABILITY',
        ],
        'PURCHASE_ORDER' => [
            'PURCHASE_ORDER',
            'CONTRACT_OF_SALES',
            'PROFORMA_INVOICE',
        ],
        'TRANSPORT_ORDER' => [
            'TRANSPORT_ORDER',
            'DELIVERY_NOTE',
            'SPECIMEN_CONTRACT',
        ],
        'SHIPPING_ORDER' => [
            'COMMERCIAL_INVOICE',
            'PACKING_LIST',
            'EXPORT_LIABILITY',
        ],
        'ORDER_COMPLETION' => [
            'COMMERCIAL_INVOICE',
            'PACKING_LIST',
            'DELIVERY_NOTE',
        ],
    ];

    /**
     * Ensure the contract has default documents for its current stage.
     */
    public function ensureDefaultDocumentsForContract(Contract $contract, ?int $userId = null): Collection
    {
        $orderCode = $contract->orderCode ?? $contract->order_code ?? ('DOC' . $contract->id);
        $stageName = strtoupper($contract->stage?->description ?? $contract->stage?->name ?? 'SALES_ORDER');

        $docTypes = self::STAGE_DOCUMENTS['SALES_ORDER']; // default
        if (str_contains($stageName, 'PURCHASE')) {
            $docTypes = self::STAGE_DOCUMENTS['PURCHASE_ORDER'];
        } elseif (str_contains($stageName, 'TRANSPORT')) {
            $docTypes = self::STAGE_DOCUMENTS['TRANSPORT_ORDER'];
        } elseif (str_contains($stageName, 'SHIPPING')) {
            $docTypes = self::STAGE_DOCUMENTS['SHIPPING_ORDER'];
        } elseif (str_contains($stageName, 'COMPLETION')) {
            $docTypes = self::STAGE_DOCUMENTS['ORDER_COMPLETION'];
        }

        $existingFiles = $contract->generatedFiles()->get();
        $existingNames = $existingFiles->map(fn($f) => strtoupper($f->name))->all();

        $created = collect();
        foreach ($docTypes as $type) {
            $docName = $type . '_' . $orderCode;

            // Check if already exists
            $alreadyExists = false;
            foreach ($existingNames as $en) {
                if (str_starts_with($en, $type) && str_contains($en, $orderCode)) {
                    $alreadyExists = true;
                    break;
                }
            }

            if (!$alreadyExists) {
                // Find template if available
                $template = Template::where('type', $type)->first();
                $file = $this->createDocumentOfType($type, $contract, $template, $userId);
                $created->push($file);
            }
        }

        return $contract->generatedFiles()->get();
    }

    /**
     * Create document of a specific type.
     */
    public function createDocumentOfType(string $type, Contract $contract, ?Template $template = null, ?int $userId = null): GeneratedFile
    {
        $orderCode = $contract->orderCode ?? $contract->order_code ?? ('DOC' . $contract->id);
        $name = $type . '_' . $orderCode;

        $html = '';
        if ($template && !empty($template->html)) {
            $html = $this->solveReplacements($template->html, $contract);
        } else {
            $html = $this->generateDefaultHtmlForType($type, $contract);
        }

        $data = [
            'contract_id' => $contract->id,
            'template_id' => $template?->id,
        ];

        if (\Illuminate\Support\Facades\Schema::hasColumn('generatedfiles', 'name')) {
            $data['name'] = $name;
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('generatedfiles', 'fileName')) {
            $data['fileName'] = $name . '.html';
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('generatedfiles', 'content')) {
            $data['content'] = base64_encode($html);
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('generatedfiles', 'html')) {
            $data['html'] = $html;
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('generatedfiles', 'active')) {
            $data['active'] = true;
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('generatedfiles', 'fileType')) {
            $data['fileType'] = 'HTML';
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('generatedfiles', 'user_id')) {
            $data['user_id'] = $userId ?: (Auth::id() ?: 1);
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('generatedfiles', 'version')) {
            $data['version'] = 1;
        }

        return GeneratedFile::create($data);
    }

    /**
     * Solve and render a template for a contract.
     */
    public function render(Template $template, Contract $contract, ?int $userId = null): GeneratedFile
    {
        return $this->createDocumentOfType($template->type, $contract, $template, $userId);
    }

    /**
     * Regenerate an existing GeneratedFile from latest contract data.
     */
    public function regenerate(GeneratedFile $file, Contract $contract, ?int $userId = null): GeneratedFile
    {
        $type = $this->extractTypeFromFileName($file->name);
        $template = $file->template ?: Template::where('type', $type)->first();

        $html = '';
        if ($template && !empty($template->html)) {
            $html = $this->solveReplacements($template->html, $contract);
        } else {
            $html = $this->generateDefaultHtmlForType($type, $contract);
        }

        if (\Illuminate\Support\Facades\Schema::hasColumn('generatedfiles', 'html')) {
            $file->html = $html;
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('generatedfiles', 'content')) {
            $file->content = base64_encode($html);
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('generatedfiles', 'version') && isset($file->version)) {
            $file->version = ($file->version ?? 0) + 1;
        }
        $file->save();

        return $file;
    }

    /**
     * Extract document type prefix from document name.
     */
    public function extractTypeFromFileName(string $name): string
    {
        $types = [
            'PROFORMA_INVOICE',
            'DELIVERY_NOTE',
            'CONTRACT_OF_SALES',
            'SPECIMEN_CONTRACT',
            'EXPORT_LIABILITY',
            'PURCHASE_ORDER',
            'TRANSPORT_ORDER',
            'COMMERCIAL_INVOICE',
            'PACKING_LIST',
            'CREDIT_NOTE',
            'DEBIT_NOTE',
        ];

        foreach ($types as $t) {
            if (str_starts_with(strtoupper($name), $t)) {
                return $t;
            }
        }

        return 'CONTRACT_OF_SALES';
    }

    /**
     * Solve template string placeholders with contract data.
     */
    protected function solveReplacements(string $rawHtml, Contract $contract): string
    {
        $deal = $contract->sale ?: ($contract->purchase ?: null);
        $purchaseDeal = $contract->purchase;
        $saleDeal = $contract->sale;

        $metaCompany = $deal?->metaCompany ?? $contract->purchase?->metaCompany ?? $contract->sale?->metaCompany;
        $contact = $deal?->contact;
        $address = $deal?->address;
        $currency = $deal?->currency?->description ?? 'USD';
        $orderCode = $contract->orderCode ?? $contract->order_code ?? 'CONTRACT';
        $orderDate = $contract->due_date ? \Carbon\Carbon::parse($contract->due_date)->format('d/m/Y') : date('d/m/Y');

        $dealProducts = $deal ? ($deal->products ?? collect()) : collect();
        $totalAmount = number_format($dealProducts->sum('total_price') ?: ($deal?->total ?? 0), 2) . ' ' . $currency;

        // Build Product Table HTML
        $productTable = '<table style="width:100%; border-collapse:collapse; margin:16px 0; font-size:13px;">'
            . '<thead><tr style="background:#f1f5f9; text-align:left; border-bottom:2px solid #cbd5e1;">'
            . '<th style="padding:10px 12px; border:1px solid #cbd5e1; font-weight:600;">Product</th>'
            . '<th style="padding:10px 12px; border:1px solid #cbd5e1; font-weight:600; text-align:right;">Quantity (MT)</th>'
            . '<th style="padding:10px 12px; border:1px solid #cbd5e1; font-weight:600; text-align:right;">Unit Price</th>'
            . '<th style="padding:10px 12px; border:1px solid #cbd5e1; font-weight:600; text-align:right;">Total</th>'
            . '</tr></thead><tbody>';

        if ($dealProducts->isNotEmpty()) {
            foreach ($dealProducts as $p) {
                $pName = htmlspecialchars($p->product?->name ?? 'Commodity Product');
                $qty = number_format($p->quantity ?? 0, 3);
                $uprice = number_format($p->unit_price ?? $p->unitPrice ?? 0, 2) . ' ' . $currency;
                $ptot = number_format($p->total_price ?? $p->total ?? 0, 2) . ' ' . $currency;
                $productTable .= "<tr style=\"border-bottom:1px solid #e2e8f0;\">"
                    . "<td style=\"padding:9px 12px; border:1px solid #cbd5e1;\">{$pName}</td>"
                    . "<td style=\"padding:9px 12px; border:1px solid #cbd5e1; text-align:right;\">{$qty}</td>"
                    . "<td style=\"padding:9px 12px; border:1px solid #cbd5e1; text-align:right;\">{$uprice}</td>"
                    . "<td style=\"padding:9px 12px; border:1px solid #cbd5e1; text-align:right; font-weight:600;\">{$ptot}</td></tr>";
            }
        } else {
            $productTable .= '<tr><td colspan="4" style="padding:16px; text-align:center; color:#64748b; border:1px solid #cbd5e1;">No products specified</td></tr>';
        }
        $productTable .= '</tbody></table>';

        $replacements = [
            '{orderCode}' => $orderCode,
            '{contract_order_code}' => $orderCode,
            '{orderDate}' => $orderDate,
            '{contract_date}' => $orderDate,
            '{companyName}' => $metaCompany?->name ?? 'Meta Enterprises Ltd',
            '{meta_company_name}' => $metaCompany?->name ?? 'Meta Enterprises Ltd',
            '{companyVat}' => $metaCompany?->vatNumber ?? 'GB123456789',
            '{meta_company_vat}' => $metaCompany?->vatNumber ?? 'GB123456789',
            '{companyAddress}' => $metaCompany?->address?->addressLine1 ?? '100 London Wall, London, EC2M 5QQ',
            '{meta_company_address_line1}' => $metaCompany?->address?->addressLine1 ?? '100 London Wall',
            '{meta_company_address_city}' => $metaCompany?->address?->city ?? 'London',
            '{buyerName}' => $contact?->name ?? 'Client Partner',
            '{contract_buyer_name}' => $contact?->name ?? 'Client Partner',
            '{buyerAddress}' => $address?->addressLine1 ?? '',
            '{contract_buyer_address_line1}' => $address?->addressLine1 ?? '',
            '{buyerCity}' => $address?->city ?? '',
            '{contract_buyer_address_city}' => $address?->city ?? '',
            '{buyerCountry}' => $address?->country?->name ?? 'United Kingdom',
            '{contract_buyer_country}' => $address?->country?->name ?? 'United Kingdom',
            '{sellerName}' => $metaCompany?->name ?? 'Meta Enterprises Ltd',
            '{deliveryTerms}' => $contract->deliveryTerms ?? 'CIF',
            '{contract_delivery_terms}' => $contract->deliveryTerms ?? 'CIF',
            '{modeOfPayment}' => $contract->modeOfPayment ?? 'CAD',
            '{contract_mode_of_payment}' => $contract->modeOfPayment ?? 'CAD',
            '{totalAmount}' => $totalAmount,
            '{contract_total_amount}' => $totalAmount,
            '{productTable}' => $productTable,
            '{contract_product_table}' => $productTable,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $rawHtml);
    }

    /**
     * Generate responsive, professional default HTML document when no DB template exists.
     */
    public function generateDefaultHtmlForType(string $type, Contract $contract): string
    {
        $title = ucwords(strtolower(str_replace('_', ' ', $type)));
        $orderCode = $contract->orderCode ?? $contract->order_code ?? ('DOC-' . $contract->id);
        $orderDate = $contract->due_date ? \Carbon\Carbon::parse($contract->due_date)->format('d/m/Y') : date('d/m/Y');

        $deal = $contract->sale ?: ($contract->purchase ?: null);
        $metaCompany = $deal?->metaCompany ?? $contract->purchase?->metaCompany ?? $contract->sale?->metaCompany;
        $companyName = $metaCompany?->name ?? 'META ENTERPRISES LTD';
        $companyVat = $metaCompany?->vatNumber ?? 'GB 342 9871 20';
        $companyAddress = $metaCompany?->address?->addressLine1 ?? '100 London Wall, London, EC2M 5QQ, United Kingdom';
        $companyEmail = $metaCompany?->email ?? 'trade@metaenterprises.co.uk';

        $partner = $deal?->contact;
        $partnerName = $partner?->name ?? 'Valued Partner / Counterparty';
        $partnerAddress = $deal?->address?->addressLine1 ?? 'Business Trade Address';
        $partnerCity = $deal?->address?->city ?? 'London';
        $partnerCountry = $deal?->address?->country?->name ?? 'United Kingdom';

        $currency = $deal?->currency?->description ?? 'USD';
        $dealProducts = $deal ? ($deal->products ?? collect()) : collect();
        $totalAmount = number_format($dealProducts->sum('total_price') ?: ($deal?->total ?? 0), 2) . ' ' . $currency;

        $productRows = '';
        if ($dealProducts->isNotEmpty()) {
            foreach ($dealProducts as $p) {
                $pName = htmlspecialchars($p->product?->name ?? 'Metal Scrap / Alloy');
                $qty = number_format($p->quantity ?? 0, 3) . ' MT';
                $price = number_format($p->unit_price ?? $p->unitPrice ?? 0, 2) . ' ' . $currency;
                $tot = number_format($p->total_price ?? $p->total ?? 0, 2) . ' ' . $currency;
                $productRows .= "<tr>
                    <td style=\"padding:10px 12px; border:1px solid #cbd5e1;\">{$pName}</td>
                    <td style=\"padding:10px 12px; border:1px solid #cbd5e1; text-align:right;\">{$qty}</td>
                    <td style=\"padding:10px 12px; border:1px solid #cbd5e1; text-align:right;\">{$price}</td>
                    <td style=\"padding:10px 12px; border:1px solid #cbd5e1; text-align:right; font-weight:bold;\">{$tot}</td>
                </tr>";
            }
        } else {
            $productRows = '<tr><td colspan="4" style="padding:16px; text-align:center; color:#64748b; border:1px solid #cbd5e1;">Non-ferrous / Ferrous scrap materials as per agreed specifications.</td></tr>';
        }

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{$title} - {$orderCode}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; color: #1e293b; background: #ffffff; margin: 0; padding: 32px; line-height: 1.5; font-size: 13px; }
        .doc-header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #4f46e5; padding-bottom: 20px; margin-bottom: 24px; }
        .company-info h1 { font-size: 20px; font-weight: 800; color: #1e1b4b; margin: 0 0 6px 0; text-transform: uppercase; letter-spacing: 0.5px; }
        .company-info p { margin: 2px 0; font-size: 12px; color: #475569; }
        .doc-meta { text-align: right; }
        .doc-badge { display: inline-block; background: #e0e7ff; color: #3730a3; font-weight: 700; font-size: 14px; padding: 6px 14px; border-radius: 6px; text-transform: uppercase; margin-bottom: 8px; }
        .doc-meta p { margin: 3px 0; font-size: 12px; color: #475569; }
        .parties-grid { display: flex; gap: 24px; margin-bottom: 24px; }
        .party-box { flex: 1; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; background: #f8fafc; }
        .party-box h3 { font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700; margin: 0 0 8px 0; letter-spacing: 0.5px; }
        .party-box .party-name { font-size: 14px; font-weight: 700; color: #0f172a; margin: 0 0 4px 0; }
        .party-box p { margin: 2px 0; font-size: 12px; color: #475569; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        .data-table th { background: #f1f5f9; padding: 10px 12px; border: 1px solid #cbd5e1; font-weight: 600; text-align: left; font-size: 12px; }
        .data-table td { padding: 10px 12px; border: 1px solid #cbd5e1; }
        .total-box { margin-left: auto; width: 280px; margin-bottom: 30px; }
        .total-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 13px; }
        .total-row.grand { font-size: 15px; font-weight: 800; border-top: 2px solid #0f172a; border-bottom: 2px solid #0f172a; padding: 8px 0; color: #0f172a; }
        .terms-box { border: 1px dashed #cbd5e1; border-radius: 8px; padding: 14px 18px; margin-bottom: 32px; background: #fafafa; font-size: 12px; }
        .terms-box h4 { margin: 0 0 6px 0; font-size: 12px; color: #334155; }
        .signatures { display: flex; justify-content: space-between; margin-top: 48px; padding-top: 24px; }
        .signature-block { width: 42%; border-top: 1px solid #94a3b8; padding-top: 10px; font-size: 12px; color: #475569; }
        .signature-block .sign-title { font-weight: 700; color: #0f172a; margin-bottom: 4px; }
    </style>
</head>
<body>
    <div class="doc-header">
        <div class="company-info">
            <h1>{$companyName}</h1>
            <p>{$companyAddress}</p>
            <p>VAT Number: {$companyVat} | Email: {$companyEmail}</p>
        </div>
        <div class="doc-meta">
            <div class="doc-badge">{$title}</div>
            <p><strong>Order Code:</strong> {$orderCode}</p>
            <p><strong>Date:</strong> {$orderDate}</p>
            <p><strong>Status:</strong> Official ERP Generated</p>
        </div>
    </div>

    <div class="parties-grid">
        <div class="party-box">
            <h3>Issued By (Seller / Originator)</h3>
            <p class="party-name">{$companyName}</p>
            <p>{$companyAddress}</p>
            <p>VAT: {$companyVat}</p>
        </div>
        <div class="party-box">
            <h3>Issued To (Buyer / Consignee)</h3>
            <p class="party-name">{$partnerName}</p>
            <p>{$partnerAddress}, {$partnerCity}</p>
            <p>{$partnerCountry}</p>
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>Description / Product</th>
                <th style="text-align: right;">Quantity</th>
                <th style="text-align: right;">Unit Price</th>
                <th style="text-align: right;">Total Amount</th>
            </tr>
        </thead>
        <tbody>
            {$productRows}
        </tbody>
    </table>

    <div class="total-box">
        <div class="total-row">
            <span>Subtotal:</span>
            <span>{$totalAmount}</span>
        </div>
        <div class="total-row">
            <span>Tax / VAT (0% Export):</span>
            <span>0.00 {$currency}</span>
        </div>
        <div class="total-row grand">
            <span>Total Payable:</span>
            <span>{$totalAmount}</span>
        </div>
    </div>

    <div class="terms-box">
        <h4>Terms & Conditions</h4>
        <p style="margin: 0;">This document is subject to the standard International Commercial Terms (INCOTERMS 2020) and the laws and jurisdiction of England and Wales. Quantity and quality are verified at the load port unless specified otherwise in the master contract.</p>
    </div>

    <div class="signatures">
        <div class="signature-block">
            <div class="sign-title">Authorized Signature</div>
            <div>For and on behalf of {$companyName}</div>
        </div>
        <div class="signature-block" style="text-align: right;">
            <div class="sign-title">Accepted & Confirmed By</div>
            <div>For and on behalf of {$partnerName}</div>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
