<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Template;
use App\Models\GeneratedFile;
use App\Models\User;
use App\Models\Company;
use App\Models\Archive;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

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
     * Cache for base64 encoded logo and footer
     */
    protected static ?string $logoBase64 = null;
    protected static ?string $footerBase64 = null;

    public static function getLogoDataUri(): string
    {
        if (self::$logoBase64 === null) {
            $path = public_path('assets/images/logo/metaenterprises-logo.png');
            if (file_exists($path)) {
                self::$logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($path));
            } else {
                self::$logoBase64 = '';
            }
        }
        return self::$logoBase64;
    }

    public static function getFooterDataUri(): string
    {
        if (self::$footerBase64 === null) {
            $path = public_path('assets/images/logo/metaenterprises-footer.png');
            if (file_exists($path)) {
                self::$footerBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($path));
            } else {
                self::$footerBase64 = '';
            }
        }
        return self::$footerBase64;
    }

    /**
     * Populate base64 data URIs into <img> tags in document HTML,
     * replicating Java's ArchiveService.populateBase64inHtml logic.
     */
    public static function populateBase64inHtml(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        $defaultLogo = self::getLogoDataUri();
        $defaultFooter = self::getFooterDataUri();

        // Process all <img> tags
        return preg_replace_callback('/<img\b([^>]*)>/i', function ($match) use ($defaultLogo, $defaultFooter) {
            $attrs = $match[1];

            // Extract id attribute
            $imgId = null;
            if (preg_match('/\bid=(["\'])(.*?)\1/i', $attrs, $mId)) {
                $imgId = trim($mId[2]);
            }

            // Extract src attribute
            $src = '';
            if (preg_match('/\bsrc=(["\'])(.*?)\1/i', $attrs, $mSrc)) {
                $src = trim($mSrc[2]);
            }

            // Extract alt attribute
            $alt = '';
            if (preg_match('/\balt=(["\'])(.*?)\1/i', $attrs, $mAlt)) {
                $alt = trim($mAlt[2]);
            }

            // If src already has data:image, preserve it
            if (str_starts_with($src, 'data:image')) {
                return $match[0];
            }

            $newSrc = null;

            // 1. If ID is numeric, query archive table
            if (!empty($imgId) && is_numeric($imgId)) {
                try {
                    $archive = \App\Models\Archive::find((int) $imgId);
                    if ($archive && !empty($archive->content)) {
                        $content = $archive->content;
                        if (str_starts_with($content, 'data:image')) {
                            $newSrc = $content;
                        } else {
                            $newSrc = 'data:image/png;base64,' . $content;
                        }
                    }
                } catch (\Throwable $e) {
                    // ignore
                }
            }

            // 2. If ID or alt indicates logo
            if (empty($newSrc)) {
                if ($imgId === '{company_url_logo}' || $imgId === 'company_url_logo' || 
                    stripos($alt, 'logo') !== false || stripos($alt, 'metaenterprises') !== false ||
                    str_contains($src, 'metaenterprices.png')) {
                    $newSrc = $defaultLogo;
                }
            }

            // 3. If ID or alt indicates footer
            if (empty($newSrc)) {
                if ($imgId === '{company_url_footer}' || $imgId === 'company_url_footer' || 
                    stripos($alt, 'rodape') !== false || stripos($alt, 'footer') !== false ||
                    str_contains($src, 'footer.png')) {
                    $newSrc = $defaultFooter;
                }
            }

            // 4. If ID or alt indicates stamp
            if (empty($newSrc)) {
                if ($imgId === '{company_stamp}' || $imgId === 'company_stamp' || stripos($alt, 'stamp') !== false) {
                    $defStamp = \App\Models\Archive::find(9) ?: \App\Models\Archive::find(2);
                    if ($defStamp && !empty($defStamp->content)) {
                        $newSrc = 'data:image/jpeg;base64,' . $defStamp->content;
                    }
                }
            }

            // 5. If ID or alt indicates signature
            if (empty($newSrc)) {
                if ($imgId === '{user_signature}' || $imgId === 'user_signature' || stripos($alt, 'signature') !== false) {
                    $defSig = \App\Models\Archive::find(6) ?: \App\Models\Archive::find(1);
                    if ($defSig && !empty($defSig->content)) {
                        $newSrc = 'data:image/png;base64,' . $defSig->content;
                    }
                }
            }

            // If we found a valid replacement source, update or insert src
            if (!empty($newSrc)) {
                if (preg_match('/\bsrc=(["\']).*?\1/i', $attrs)) {
                    $newAttrs = preg_replace('/\bsrc=(["\']).*?\1/i', 'src="' . $newSrc . '"', $attrs);
                } else {
                    $newAttrs = $attrs . ' src="' . $newSrc . '"';
                }
                return '<img' . $newAttrs . '>';
            }

            return $match[0];
        }, $html);
    }

    /**
     * Ensure the contract has default documents for its current stage.
     */
    public function ensureDefaultDocumentsForContract(Contract $contract, ?int $userId = null): Collection
    {
        $orderCode = $contract->orderCode ?? $contract->order_code ?? ('DOC' . $contract->id);
        $stageName = strtoupper($contract->stage?->description ?? $contract->stage?->name ?? 'SALES_ORDER');

        $docTypes = self::STAGE_DOCUMENTS['SALES_ORDER'];
        if (str_contains($stageName, 'PURCHASE')) {
            $docTypes = self::STAGE_DOCUMENTS['PURCHASE_ORDER'];
        } elseif (str_contains($stageName, 'TRANSPORT')) {
            $docTypes = self::STAGE_DOCUMENTS['TRANSPORT_ORDER'];
        } elseif (str_contains($stageName, 'SHIPPING')) {
            $docTypes = self::STAGE_DOCUMENTS['SHIPPING_ORDER'];
        } elseif (str_contains($stageName, 'COMPLETION')) {
            $docTypes = self::STAGE_DOCUMENTS['ORDER_COMPLETION'];
            if (method_exists($contract, 'isCreditNote') && $contract->isCreditNote()) {
                $docTypes[] = 'CREDIT_NOTE';
            }
            if (method_exists($contract, 'isDebitNote') && $contract->isDebitNote()) {
                $docTypes[] = 'DEBIT_NOTE';
            }
        }

        $existingFiles = $contract->generatedFiles()->get();
        $existingNames = $existingFiles->map(fn($f) => strtoupper($f->name))->all();

        $created = collect();
        foreach ($docTypes as $type) {
            $docName = $type . '_' . $orderCode;

            $alreadyExists = false;
            foreach ($existingNames as $en) {
                if (str_starts_with($en, $type) && str_contains($en, $orderCode)) {
                    $alreadyExists = true;
                    break;
                }
            }

            if (!$alreadyExists) {
                $template = Template::where('type', $type)->first();
                $file = $this->createDocumentOfType($type, $contract, $template, $userId);
                $created->push($file);
            }
        }

        // Ensure all detached notes for this contract have their generated files
        try {
            $detachedNotes = \App\Models\DetachedNote::where('contract_id', $contract->id)->get();
            foreach ($detachedNotes as $dNote) {
                if (!$dNote->generated_file_id || !\App\Models\GeneratedFile::where('id', $dNote->generated_file_id)->exists()) {
                    $dNote->load(['details.dealProduct.product', 'details.creditDebitType', 'contract.purchase', 'contract.sale']);
                    $this->generateForDetachedNote($dNote, $userId);
                }
            }
        } catch (\Throwable $e) {
            \Log::error('Error ensuring detached notes for contract ' . $contract->id . ': ' . $e->getMessage());
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
            $html = $this->solveReplacements($template->html, $contract, $type);
        } else {
            $html = $this->generateDefaultHtmlForType($type, $contract);
        }

        $data = [
            'contract_id' => $contract->id,
            'template_id' => $template?->id,
        ];

        if (Schema::hasColumn('generatedfiles', 'name')) {
            $data['name'] = $name;
        }
        if (Schema::hasColumn('generatedfiles', 'fileName')) {
            $data['fileName'] = $name . '.html';
        }
        if (Schema::hasColumn('generatedfiles', 'content')) {
            $data['content'] = base64_encode($html);
        }
        if (Schema::hasColumn('generatedfiles', 'html')) {
            $data['html'] = $html;
        }
        if (Schema::hasColumn('generatedfiles', 'active')) {
            $data['active'] = true;
        }
        if (Schema::hasColumn('generatedfiles', 'fileType')) {
            $data['fileType'] = 'HTML';
        }
        if (Schema::hasColumn('generatedfiles', 'user_id')) {
            $data['user_id'] = $userId ?: (Auth::id() ?: 1);
        }
        if (Schema::hasColumn('generatedfiles', 'version')) {
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
     * Generate or update a GeneratedFile document for a DetachedNote (Credit Note or Debit Note).
     */
    public function generateForDetachedNote(\App\Models\DetachedNote $note, ?int $userId = null): GeneratedFile
    {
        $note->loadMissing([
            'contract.sale.metaCompany',
            'contract.sale.contact',
            'contract.sale.address.country',
            'contract.sale.currency',
            'contract.purchase.metaCompany',
            'contract.purchase.contact',
            'contract.purchase.address.country',
            'contract.purchase.currency',
            'details.dealProduct.product',
            'details.creditDebitType'
        ]);

        $contract = $note->contract;
        if (!$contract) {
            throw new \InvalidArgumentException("DetachedNote has no contract assigned");
        }

        $isCredit = strtoupper($note->kind ?? '') === 'CREDIT';
        $templateType = $isCredit ? 'CREDIT_NOTE_DETACHED' : 'DEBIT_NOTE_DETACHED';
        $subTemplateType = $isCredit ? 'PRODUCT_CREDIT_NOTE_DETACHED' : 'PRODUCT_DEBIT_NOTE_DETACHED';
        $docPrefix = $isCredit ? 'CREDIT_NOTE_' : 'DEBIT_NOTE_';

        $template = Template::where('type', $templateType)->first()
            ?: Template::where('type', $isCredit ? 'CREDIT_NOTE' : 'DEBIT_NOTE')->first();

        $subTemplate = Template::where('type', $subTemplateType)->first()
            ?: Template::where('type', $isCredit ? 'PRODUCT_CREDIT_NOTE' : 'PRODUCT_DEBIT_NOTE')->first();

        $saleDeal = $contract->sale;
        $purchaseDeal = $contract->purchase;
        $deal = $saleDeal ?: $purchaseDeal;

        $saleMeta = $deal?->metaCompany;
        $saleContact = $deal?->contact;
        $saleAddress = $deal?->address;
        $currency = $deal?->currency?->encode_html_symbol ?? '$';

        $subHtml = $subTemplate ? $subTemplate->html : '';
        $rowsHtml = '';
        $totalAmount = 0.0;
        $rowNo = 1;

        foreach ($note->details as $d) {
            $amount = (float)($d->amount ?? 0);
            $totalAmount += $amount;
            $prodName = $d->dealProduct?->product?->name ?? ($d->dealProduct?->description ?? 'Product');
            $prodCode = $d->dealProduct?->product?->code ?? '';
            $reasonText = ($d->creditDebitType?->description ?? '')
                . ($d->container ? ' - ' . $d->container : '')
                . ($d->complement ? ' - ' . $d->complement : '');

            if (!empty($subHtml)) {
                $row = $subHtml;
                if ($isCredit) {
                    $row = str_replace('{product_credit_note_number}', (string)$rowNo, $row);
                    $row = str_replace('{product_credit_note_code}', (string)$prodCode, $row);
                    $row = str_replace('{product_credit_note_description}', htmlspecialchars($prodName), $row);
                    $row = str_replace('{reason}', htmlspecialchars($reasonText), $row);
                    $row = str_replace('{product_credit_note_quantity}', '', $row);
                    $row = str_replace('{product_credit_note_price}', '', $row);
                    $row = str_replace('{product_credit_note_amount}', $currency . ' ' . number_format($amount, 2), $row);
                } else {
                    $row = str_replace('{product_debit_note_number}', (string)$rowNo, $row);
                    $row = str_replace('{product_debit_note_code}', (string)$prodCode, $row);
                    $row = str_replace('{product_debit_note_description}', htmlspecialchars($prodName), $row);
                    $row = str_replace('{reason}', htmlspecialchars($reasonText), $row);
                    $row = str_replace('{product_debit_note_quantity}', '', $row);
                    $row = str_replace('{product_debit_note_price}', '', $row);
                    $row = str_replace('{product_debit_note_amount}', $currency . ' ' . number_format($amount, 2), $row);
                }
                $rowsHtml .= $row;
            }
            $rowNo++;
        }

        $rawHtml = $template ? $template->html : $this->generateDefaultHtmlForType($templateType, $contract);
        if ($isCredit) {
            $rawHtml = str_replace('[{credit_note:product_of_the_credit_note}]', $rowsHtml, $rawHtml);
        } else {
            $rawHtml = str_replace('[{debit_note:product_of_the_debit_note}]', $rowsHtml, $rawHtml);
        }

        $cinvFormatted = $contract->commercial_invoice_number_formatted ?? $contract->commercialInvoiceNumberFormatted ?? '';
        $cinvNum = $cinvFormatted ? preg_replace('/[^0-9-]/', '', $cinvFormatted) : $contract->order_code;
        $noteNumber = ($isCredit ? 'CN' : 'DN') . $cinvNum . '-' . $note->id;

        // Resolve authorising user, signature, title, and company stamp
        $authUser = null;
        if ($userId) {
            $authUser = User::find($userId);
        }
        if (!$authUser && Auth::check()) {
            $authUser = Auth::user();
        }
        if (!$authUser) {
            $authUser = User::whereNotNull('archive_signature_id')->first() ?: User::find(1);
        }

        $authName = trim($authUser?->name ?? 'Authorised Signatory');
        $authRole = $authUser?->occupation ?: ($authUser?->role ?: 'Authorised Signatory');
        $authEmail = $authUser?->email ?? ($authUser?->username ?? '');

        // Resolve user signature image data URI
        $userSigUri = '';
        if ($authUser?->archive_signature_id) {
            $archSig = \App\Models\Archive::find($authUser->archive_signature_id);
            if ($archSig && !empty($archSig->content)) {
                $userSigUri = str_starts_with($archSig->content, 'data:image') 
                    ? $archSig->content 
                    : ('data:image/png;base64,' . $archSig->content);
            }
        }
        if (empty($userSigUri) && !empty($authUser?->signature_file)) {
            $userSigUri = str_starts_with($authUser->signature_file, 'data:image') 
                ? $authUser->signature_file 
                : ('data:image/png;base64,' . $authUser->signature_file);
        }

        // Resolve company stamp image data URI
        $companyStampUri = '';
        if ($saleMeta?->archive_stamp_id) {
            $archStamp = \App\Models\Archive::find($saleMeta->archive_stamp_id);
            if ($archStamp && !empty($archStamp->content)) {
                $companyStampUri = str_starts_with($archStamp->content, 'data:image') 
                    ? $archStamp->content 
                    : ('data:image/jpeg;base64,' . $archStamp->content);
            }
        }
        if (empty($companyStampUri) && !empty($saleMeta?->stamp_file)) {
            $companyStampUri = str_starts_with($saleMeta->stamp_file, 'data:image') 
                ? $saleMeta->stamp_file 
                : ('data:image/jpeg;base64,' . $saleMeta->stamp_file);
        }
        if (empty($companyStampUri)) {
            $defaultStampArch = \App\Models\Archive::find(9) ?: \App\Models\Archive::find(2);
            if ($defaultStampArch && !empty($defaultStampArch->content)) {
                $companyStampUri = 'data:image/jpeg;base64,' . $defaultStampArch->content;
            }
        }

        $companyName = $saleMeta?->name ?? 'Metaenterprices LTD';
        $currentDate = date('d/m/Y');

        // Formatted Authorised Person, Stamp, and Signature Block
        $signatureBlockRow = '<tr style="height:115pt">'
            . '<td style="width:485pt; border:2pt solid black; padding: 12px 18px; vertical-align: top;" colspan="8">'
            . '<table style="width: 100%; border: none; border-collapse: collapse;">'
            . '<tr>'
            . '<td style="width: 55%; vertical-align: top; border: none; font-size: 11px; line-height: 1.4em;">'
            . '<div style="font-weight: bold; color: #1e293b; margin-bottom: 6px; font-size: 12px;">'
            . 'Authorised &amp; Approved by:'
            . '</div>'
            . '<div style="min-height: 48px; margin-bottom: 6px;">'
            . ($userSigUri ? '<img src="' . $userSigUri . '" id="user_signature" style="height: 18mm; max-width: 60mm; object-fit: contain;" alt="Signature" />' : '<div style="height: 16mm;"></div>')
            . '</div>'
            . '<div style="font-weight: bold; color: #0f172a; font-size: 13px;">'
            . htmlspecialchars($authName)
            . '</div>'
            . '<div style="color: #475569; font-size: 11px; margin-top: 1px;">'
            . htmlspecialchars($authRole)
            . '</div>'
            . '<div style="color: #64748b; font-size: 10px; margin-top: 2px;">'
            . 'For and on behalf of <strong>' . htmlspecialchars($companyName) . '</strong>'
            . '</div>'
            . '</td>'
            . '<td style="width: 45%; vertical-align: top; text-align: right; border: none; font-size: 11px; line-height: 1.4em;">'
            . '<div style="font-weight: bold; color: #1e293b; margin-bottom: 6px; font-size: 12px;">'
            . 'Official Company Stamp:'
            . '</div>'
            . '<div style="min-height: 48px; margin-bottom: 6px;">'
            . ($companyStampUri ? '<img src="' . $companyStampUri . '" id="company_stamp" style="height: 22mm; max-width: 65mm; object-fit: contain;" alt="Stamp" />' : '<div style="height: 20mm;"></div>')
            . '</div>'
            . '<div style="color: #475569; font-size: 11px; margin-top: 4px;">'
            . 'Date: <span style="font-weight: 600; color: #0f172a;">' . $currentDate . '</span>'
            . '</div>'
            . '</td>'
            . '</tr>'
            . '</table>'
            . '</td>'
            . '</tr>';

        // Embed signature block in place of the empty 250pt bottom row
        if (preg_match('/<tr style="height:122pt">\s*<td[^>]*colspan="8"[^>]*><\/td>\s*<\/tr>/is', $rawHtml)) {
            $rawHtml = preg_replace('/<tr style="height:122pt">\s*<td[^>]*colspan="8"[^>]*><\/td>\s*<\/tr>/is', $signatureBlockRow, $rawHtml);
        } else {
            // Fallback: if template uses slightly different height or whitespace
            $rawHtml = preg_replace('/<tr[^>]*>\s*<td[^>]*height:250pt[^>]*colspan="8"[^>]*><\/td>\s*<\/tr>/is', $signatureBlockRow, $rawHtml);
        }

        $replacements = [
            '{company_url_logo}' => self::getLogoDataUri(),
            '{company_url_footer}' => self::getFooterDataUri(),
            '{user_signature}' => $userSigUri,
            '{company_stamp}' => $companyStampUri,
            '{authorised_person_name}' => $authName,
            '{approved_by}' => $authName,
            '{user_name}' => $authName,
            '{user_email}' => $authEmail,
            '{user_occupation}' => $authRole,
            '{meta_company_name}' => $saleMeta?->name ?? 'Metaenterprices LTD',
            '{meta_company_number}' => $saleMeta?->company_number ?? '',
            '{meta_company_vat}' => $saleMeta?->vat_number ?? '',
            '{meta_company_address_line1}' => $saleMeta?->address?->address_line1 ?? ($saleMeta?->address?->addressLine1 ?? ''),
            '{meta_company_address_line2}' => $saleMeta?->address?->address_line2 ?? ($saleMeta?->address?->addressLine2 ?? ''),
            '{meta_company_address_postal_code}' => $saleMeta?->address?->postal_code ?? ($saleMeta?->address?->postalCode ?? ''),
            '{meta_company_address_city}' => $saleMeta?->address?->city ?? '',
            '{meta_company_address_state}' => $saleMeta?->address?->state ?? '',
            '{meta_company_country}' => $saleMeta?->address?->country?->name ?? 'United Kingdom',
            '{meta_company_tel}' => $saleMeta?->phone_number ?? '',
            '{meta_company_email}' => $saleMeta?->email ?? '',
            '{meta_company_website}' => $saleMeta?->website ?? '',
            '{meta_company_fax}' => $saleMeta?->fax_number ?? '',
            '{meta_company_eori}' => $saleMeta?->eori_number ?? '',
            '{buyer_name}' => $saleContact?->name ?? 'Customer',
            '{buyer_address_line1}' => $saleAddress?->address_line1 ?? ($saleAddress?->addressLine1 ?? ''),
            '{buyer_address_line2}' => $saleAddress?->address_line2 ?? ($saleAddress?->addressLine2 ?? ''),
            '{buyer_address_city}' => $saleAddress?->city ?? '',
            '{buyer_address_postal_code}' => $saleAddress?->postal_code ?? ($saleAddress?->postalCode ?? ''),
            '{buyer_address_country}' => $saleAddress?->country?->name ?? '',
            '{credit_note_number}' => $noteNumber,
            '{debit_note_number}' => $noteNumber,
            '{sales_invoice_number}' => $cinvFormatted ?: $contract->order_code,
            '{current_date}' => date('d/m/Y'),
            '{contract_date}' => $contract->due_date ? date('d/m/Y', strtotime($contract->due_date)) : date('d/m/Y'),
            '{contract_code}' => $contract->order_code,
            '{contract_seller_name}' => $saleContact?->name ?? '',
            '{contract_seller_address_line1}' => $saleAddress?->address_line1 ?? ($saleAddress?->addressLine1 ?? ''),
            '{contract_seller_address_line2}' => $saleAddress?->address_line2 ?? ($saleAddress?->addressLine2 ?? ''),
            '{contract_seller_address_country}' => $saleAddress?->country?->name ?? '',
            '{contract_total_vat}' => '0',
            '{country_of_delivery_vat_rate}' => '0',
            '{country_of_origin}' => $deal?->countryOfOrigin?->name ?? '',
            '{product_credit_note_total}' => $currency . ' ' . number_format($totalAmount, 2),
            '{product_credit_note_general_total}' => $currency . ' ' . number_format($totalAmount, 2),
            '{product_debit_note_total}' => $currency . ' ' . number_format($totalAmount, 2),
            '{product_debit_note_general_total}' => $currency . ' ' . number_format($totalAmount, 2),
            '{notes}' => $note->complement ?: 'No notes',
        ];

        foreach ($replacements as $k => $val) {
            $rawHtml = str_replace($k, $val, $rawHtml);
        }

        $rawHtml = self::populateBase64inHtml($rawHtml);

        $docName = $docPrefix . $contract->order_code;

        $gf = null;
        if ($note->generated_file_id) {
            $gf = GeneratedFile::find($note->generated_file_id);
        }
        if (!$gf) {
            $gf = GeneratedFile::where('contract_id', $contract->id)->where('name', $docName)->first();
        }
        if (!$gf) {
            $gf = new GeneratedFile();
            $gf->contract_id = $contract->id;
            $gf->name = $docName;
            $gf->template_id = $template?->id;
            $gf->active = true;
            $gf->user_id = $userId ?: (Auth::id() ?: 1);
        }

        $gf->content = base64_encode($rawHtml);
        $gf->html = $rawHtml;
        $gf->active = true;
        $gf->save();

        if ($note->generated_file_id !== $gf->id) {
            $note->generated_file_id = $gf->id;
            $note->save();
        }

        return $gf;
    }

    /**
     * Regenerate an existing GeneratedFile from latest contract data.
     */
    public function regenerate(GeneratedFile $file, Contract $contract, ?int $userId = null): GeneratedFile
    {
        $type = $this->extractTypeFromFileName($file->name);

        // Special handling for Credit Note / Debit Note which are detached notes
        if ($type === 'CREDIT_NOTE' || $type === 'DEBIT_NOTE') {
            $isCredit = $type === 'CREDIT_NOTE';
            $kind = $isCredit ? 'CREDIT' : 'DEBIT';
            $dNote = \App\Models\DetachedNote::where('generated_file_id', $file->id)->first()
                ?: \App\Models\DetachedNote::where('contract_id', $contract->id)->where('kind', $kind)->first();
            if ($dNote) {
                return $this->generateForDetachedNote($dNote, $userId);
            }
        }

        $template = $file->template ?: Template::where('type', $type)->first();

        $html = '';
        if ($template && !empty($template->html)) {
            $html = $this->solveReplacements($template->html, $contract, $type);
        } else {
            $html = $this->generateDefaultHtmlForType($type, $contract);
        }

        if (Schema::hasColumn('generatedfiles', 'html')) {
            $file->html = $html;
        }
        if (Schema::hasColumn('generatedfiles', 'content')) {
            $file->content = base64_encode($html);
        }
        if (Schema::hasColumn('generatedfiles', 'version') && isset($file->version)) {
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
     * Gather all contract, company, partner, product, and bank variables.
     */
    public function getDocumentVariables(Contract $contract): array
    {
        $deal = $contract->sale ?: ($contract->purchase ?: null);
        $purchaseDeal = $contract->purchase;
        $saleDeal = $contract->sale;

        $metaCompany = $deal?->metaCompany ?? $contract->purchase?->metaCompany ?? $contract->sale?->metaCompany;
        $partner = $deal?->contact;
        $address = $deal?->address;
        $currency = $deal?->currency?->description ?? 'USD';
        $currSymbol = ($currency === 'EUR') ? '€' : (($currency === 'GBP') ? '£' : '$');
        
        $orderCode = $contract->orderCode ?? $contract->order_code ?? ('MERES26-' . $contract->id);
        $invoiceDate = $contract->due_date ? \Carbon\Carbon::parse($contract->due_date)->format('d/m/Y') : date('d/m/Y');
        
        // Products
        $dealProducts = $deal ? ($deal->products ?? collect()) : collect();
        $productsList = [];
        $subtotal = 0;
        
        if ($dealProducts->isNotEmpty()) {
            foreach ($dealProducts as $idx => $p) {
                $pName = $p->product?->name ?? 'Dross';
                $lmePerc = $p->lme_percentage ?? $p->lmePercentage ?? 100;
                $lmePrice = $p->lme_price ?? $p->lmePrice ?? 3500.00;
                $premium = $p->premium ?? 350.00;
                $qty = $p->quantity ?? 50.000;
                $uprice = $p->unit_price ?? $p->unitPrice ?? ($lmePrice * ($lmePerc / 100) + $premium);
                $amount = $p->total_price ?? $p->total ?? ($qty * $uprice);
                $subtotal += $amount;

                $productsList[] = [
                    'no' => $idx + 1,
                    'description' => $pName,
                    'lme_perc' => rtrim(rtrim(number_format($lmePerc, 2), '0'), '.') . '%',
                    'lme_price' => $currSymbol . ' ' . number_format($lmePrice, 2),
                    'premium' => number_format($premium, 2),
                    'quantity' => number_format($qty, 3),
                    'uprice' => $currSymbol . ' ' . number_format($uprice, 2),
                    'amount' => $currSymbol . ' ' . number_format($amount, 2),
                    'raw_amount' => $amount,
                ];
            }
        } else {
            // Default demo product matching realistic ERP test state
            $productsList[] = [
                'no' => 1,
                'description' => 'Dross',
                'lme_perc' => '100%',
                'lme_price' => $currSymbol . ' 3,500.00',
                'premium' => '350.00',
                'quantity' => '50.000',
                'uprice' => $currSymbol . ' 3,850.00',
                'amount' => $currSymbol . ' 192,500.00',
                'raw_amount' => 192500.00,
            ];
            $subtotal = 192500.00;
        }

        $vatRate = '0%';
        $totalVat = 0.00;
        $grandTotal = $subtotal + $totalVat;

        // Metaenterprises Company Details
        $compName = $metaCompany?->name ?: 'Metaenterprises LTD';
        $compAddr1 = $metaCompany?->address?->addressLine1 ?: '217 Marsh Wall';
        $compAddr2 = $metaCompany?->address?->addressLine2 ?: '';
        $compPostcode = $metaCompany?->address?->postal_code ?: 'E14 9FJ';
        $compCity = $metaCompany?->address?->city ?: 'London';
        $compState = $metaCompany?->address?->state ?: 'Greater London';
        $compCountry = $metaCompany?->address?->country?->name ?: 'United Kingdom';
        $compTel = $metaCompany?->phoneNumber ?: '+44 0208 5514445';
        $compEmail = $metaCompany?->email ?: 'info@metaenterprises.co.uk';
        $compWeb = $metaCompany?->webSite ?: 'www.metaenterprises.co.uk';
        $compNumber = $metaCompany?->companyNumber ?: '5833598';
        $compVat = $metaCompany?->vatNumber ?: '903687119';
        $compEori = $metaCompany?->eoriNumber ?: 'GB903687119000';

        // Buyer details (or partner)
        $buyerName = $partner?->name ?: 'Galvamed';
        $buyerAddr1 = $address?->addressLine1 ?: 'Zone Industrielle Sklrat BP4423 RN1 Pres de Oued Yquem Sebbah';
        $buyerAddr2 = $address?->addressLine2 ?: '';
        $buyerPostcode = $address?->postal_code ?: '12050';
        $buyerCity = $address?->city ?: 'SKHIRAT';
        $buyerState = $address?->state ?: 'Sebbah';
        $buyerCountry = $address?->country?->name ?: 'Morocco';
        $buyerVat = $partner?->vatNumber ?: '0019283748';

        // Order specifics
        $deliveryTerms = $contract->deliveryTerms ?: ($deal?->deliveryTerms ?: 'CFR Casablanca');
        $modeOfTransport = $contract->modeOfTransport ?: ($deal?->modeOfTransport ?: 'ROAD');
        $customerRef = $contract->customerRef ?: ($deal?->customerRef ?: '');
        $invoiceNo = 'ME-PI26-' . (400 + $contract->id);
        $offerNo = $deal?->offerCode ?: '';
        $countryOfOrigin = 'Belgium';
        $countryOfDelivery = $buyerCountry ?: 'Morocco';

        // Payment & Bank Details
        $paymentTerms = $contract->modeOfPayment ?: ($deal?->paymentTerms ?: 'Credit 30Days');
        $bankName = 'Virgin Money';
        $acNo = '10128364';
        $sortCode = '826013';
        $iban = 'GB52CLYD82601310128364';
        $swift = 'CLYDGB21028';

        // Signature & Stamp resolution for contract documents
        $stampUri = '';
        if ($metaCompany?->archive_stamp_id) {
            $archStamp = Archive::find($metaCompany->archive_stamp_id);
            if ($archStamp && !empty($archStamp->content)) {
                $stampUri = str_starts_with($archStamp->content, 'data:image') ? $archStamp->content : ('data:image/jpeg;base64,' . $archStamp->content);
            }
        }
        if (empty($stampUri) && !empty($metaCompany?->stamp_file)) {
            $stampUri = str_starts_with($metaCompany->stamp_file, 'data:image') ? $metaCompany->stamp_file : ('data:image/jpeg;base64,' . $metaCompany->stamp_file);
        }
        if (empty($stampUri)) {
            $defArch = Archive::find(9) ?: Archive::find(2);
            if ($defArch && !empty($defArch->content)) {
                $stampUri = 'data:image/jpeg;base64,' . $defArch->content;
            }
        }

        $defaultUser = User::whereNotNull('archive_signature_id')->first() ?: User::find(1);
        $sigUri = '';
        if ($defaultUser?->archive_signature_id) {
            $archSig = Archive::find($defaultUser->archive_signature_id);
            if ($archSig && !empty($archSig->content)) {
                $sigUri = str_starts_with($archSig->content, 'data:image') ? $archSig->content : ('data:image/png;base64,' . $archSig->content);
            }
        }
        if (empty($sigUri) && !empty($defaultUser?->signature_file)) {
            $sigUri = str_starts_with($defaultUser->signature_file, 'data:image') ? $defaultUser->signature_file : ('data:image/png;base64,' . $defaultUser->signature_file);
        }

        return [
            'meta' => [
                'name' => $compName,
                'address1' => $compAddr1,
                'address2' => $compAddr2,
                'postcode' => $compPostcode,
                'city' => $compCity,
                'state' => $compState,
                'country' => $compCountry,
                'tel' => $compTel,
                'email' => $compEmail,
                'web' => $compWeb,
                'company_number' => $compNumber,
                'vat' => $compVat,
                'eori' => $compEori,
                'logo_uri' => self::getLogoDataUri(),
                'footer_uri' => self::getFooterDataUri(),
                'stamp_uri' => $stampUri,
                'signature_uri' => $sigUri,
                'user_name' => $defaultUser?->name ?? 'Authorised Signatory',
                'user_email' => $defaultUser?->email ?? ($defaultUser?->username ?? ''),
            ],
            'buyer' => [
                'name' => $buyerName,
                'address1' => $buyerAddr1,
                'address2' => $buyerAddr2,
                'postcode' => $buyerPostcode,
                'city' => $buyerCity,
                'state' => $buyerState,
                'country' => $buyerCountry,
                'vat' => $buyerVat,
            ],
            'order' => [
                'code' => $orderCode,
                'invoice_no' => $invoiceNo,
                'invoice_date' => $invoiceDate,
                'delivery_terms' => $deliveryTerms,
                'customer_ref' => $customerRef,
                'mode_of_transport' => $modeOfTransport,
                'offer_no' => $offerNo,
                'country_origin' => $countryOfOrigin,
                'country_delivery' => $countryOfDelivery,
                'payment_terms' => $paymentTerms,
                'currency' => $currency,
                'curr_symbol' => $currSymbol,
                'notes' => '',
            ],
            'bank' => [
                'name' => $bankName,
                'ac_no' => $acNo,
                'sort_code' => $sortCode,
                'iban' => $iban,
                'swift' => $swift,
            ],
            'products' => $productsList,
            'totals' => [
                'subtotal' => $currSymbol . ' ' . number_format($subtotal, 2),
                'vat_rate' => $vatRate,
                'total_vat' => $currSymbol . ' ' . number_format($totalVat, 2),
                'grand_total' => $currSymbol . ' ' . number_format($grandTotal, 2),
            ],
        ];
    }

    /**
     * Generate responsive, professional default HTML document matching exact React/Java UI templates.
     */
    public function generateDefaultHtmlForType(string $type, Contract $contract): string
    {
        $v = $this->getDocumentVariables($contract);

        switch (strtoupper($type)) {
            case 'PROFORMA_INVOICE':
                return $this->buildProformaInvoiceHtml($v);
            case 'DELIVERY_NOTE':
                return $this->buildDeliveryNoteHtml($v);
            case 'CONTRACT_OF_SALES':
                return $this->buildContractOfSalesHtml($v);
            case 'SPECIMEN_CONTRACT':
                return $this->buildSpecimenContractHtml($v);
            case 'EXPORT_LIABILITY':
                return $this->buildExportLiabilityHtml($v);
            case 'PURCHASE_ORDER':
                return $this->buildPurchaseOrderHtml($v);
            case 'COMMERCIAL_INVOICE':
                return $this->buildCommercialInvoiceHtml($v);
            case 'PACKING_LIST':
                return $this->buildPackingListHtml($v);
            default:
                return $this->buildContractOfSalesHtml($v);
        }
    }

    /**
     * Solve template string placeholders with contract data.
     */
    protected function solveReplacements(string $rawHtml, Contract $contract, string $type = ''): string
    {
        $v = $this->getDocumentVariables($contract);

        // Product table rows replacement
        $proformaProdRows = '';
        foreach ($v['products'] as $p) {
            $proformaProdRows .= '<tr style="height:6mm; font-size: 7pt;line-height: 1.25em;">'
                . '<th style="border:1px black solid; padding:1mm; font-weight:900; text-align:center; width:5mm;">' . $p['no'] . '</th>'
                . '<th style="border:1px black solid; padding:1mm; font-weight:400; text-align:left; min-width:60mm;">' . htmlspecialchars($p['description']) . '</th>'
                . '<th style="border:1px black solid; padding:1mm; font-weight:400; text-align:center;">' . $p['lme_perc'] . '</th>'
                . '<th style="border:1px black solid; padding:1mm; font-weight:400; text-align:right;">' . $p['lme_price'] . '</th>'
                . '<th style="border:1px black solid; padding:1mm; font-weight:400; text-align:right;">' . $p['premium'] . '</th>'
                . '<th style="border:1px black solid; padding:1mm; font-weight:400; text-align:right;">' . $p['quantity'] . '</th>'
                . '<th style="border:1px black solid; padding:1mm; font-weight:400; text-align:right;">' . $p['uprice'] . '</th>'
                . '<th style="border:1px black solid; padding:1mm; font-weight:400; text-align:right;">' . $p['amount'] . '</th>'
                . '</tr>';
        }

        $deliveryProdRows = '';
        foreach ($v['products'] as $p) {
            $deliveryProdRows .= '<tr style="border:1px solid black">'
                . '<td style="text-align:center; border:1px solid black; padding:2mm;">' . $p['no'] . '</td>'
                . '<td style="text-align:left; border:1px solid black; padding:2mm;">' . htmlspecialchars($p['description']) . '</td>'
                . '<td style="text-align:center; border:1px solid black; padding:2mm;">' . $p['quantity'] . ' MT</td>'
                . '</tr>';
        }

        $salesContractRows = '';
        foreach ($v['products'] as $p) {
            $salesContractRows .= '<tr style="height:6mm; line-height: 1.2em;">'
                . '<th style="border:1px black solid; padding:1mm; text-align:center; width:21mm; vertical-align: top;">' . $p['no'] . '</th>'
                . '<th style="border:1px black solid; padding:1mm; text-align:left; width:21mm; vertical-align: top;">' . $v['order']['code'] . '</th>'
                . '<th style="border:1px black solid; padding:1mm; text-align:left; vertical-align: top;">' . htmlspecialchars($p['description']) . '</th>'
                . '<th style="border:1px black solid; padding:1mm; text-align:center; width:28mm; vertical-align: top;">' . $p['lme_price'] . '</th>'
                . '<th style="border:1px black solid; padding:1mm; text-align:center; width:28mm; vertical-align: top;">' . $p['premium'] . '</th>'
                . '<th style="border:1px black solid; padding:1mm; text-align:center; width:28mm; vertical-align: top;">' . $p['uprice'] . '</th>'
                . '<th style="border:1px black solid; padding:1mm; text-align:center; width:24mm; vertical-align: top;">' . $p['quantity'] . '</th>'
                . '<th style="border:1px black solid; padding:1mm; text-align:center; width:29mm; vertical-align: top;">' . $p['amount'] . '</th>'
                . '</tr>';
        }

        $replacements = [
            '{company_url_logo}' => $v['meta']['logo_uri'],
            '{company_url_footer}' => $v['meta']['footer_uri'],
            '{meta_company_name}' => $v['meta']['name'],
            '{meta_company_address_line1}' => $v['meta']['address1'],
            '{meta_company_address_line2}' => $v['meta']['address2'],
            '{meta_company_address_postal_code}' => $v['meta']['postcode'],
            '{meta_company_address_city}' => $v['meta']['city'],
            '{meta_company_address_state}' => $v['meta']['state'],
            '{meta_company_country}' => $v['meta']['country'],
            '{meta_company_tel}' => $v['meta']['tel'],
            '{meta_company_email}' => $v['meta']['email'],
            '{meta_company_website}' => $v['meta']['web'],
            '{meta_company_number}' => $v['meta']['company_number'],
            '{meta_company_vat}' => $v['meta']['vat'],
            '{meta_company_eori}' => $v['meta']['eori'],

            '{contract_seller_name}' => $v['buyer']['name'],
            '{contract_seller_address_line1}' => $v['buyer']['address1'],
            '{contract_seller_address_line2}' => $v['buyer']['address2'],
            '{contract_seller_address_postal_code}' => $v['buyer']['postcode'],
            '{contract_seller_address_city}' => $v['buyer']['city'],
            '{contract_seller_address_state}' => $v['buyer']['state'],
            '{contract_seller_address_country}' => $v['buyer']['country'],
            '{contract_seller_vat_number}' => $v['buyer']['vat'],

            '{buyer_name}' => $v['buyer']['name'],
            '{buyer_address_line1}' => $v['buyer']['address1'],
            '{buyer_address_line2}' => $v['buyer']['address2'],
            '{buyer_address_postal_code}' => $v['buyer']['postcode'],
            '{buyer_address_city}' => $v['buyer']['city'],
            '{buyer_address_state}' => $v['buyer']['state'],
            '{buyer_address_country}' => $v['buyer']['country'],
            '{buyer_vat}' => $v['buyer']['vat'],

            '{contract_delivery_terms}' => $v['order']['delivery_terms'],
            '{contract_code}' => $v['order']['code'],
            '{contract_seller_external_order_number}' => $v['order']['customer_ref'],
            '{contract_mode_of_transport}' => $v['order']['mode_of_transport'],
            '{contract_sales_offer_code}' => $v['order']['offer_no'],
            '{contract_sales_invoice_number}' => $v['order']['invoice_no'],
            '{contract_sales_invoice_date}' => $v['order']['invoice_date'],
            '{contract_country_of_origin}' => $v['order']['country_origin'],
            '{contract_country_of_delivery}' => $v['order']['country_delivery'],
            '{country_of_delivery_vat_rate}' => $v['totals']['vat_rate'],
            '{product_proforma_invoice_total}' => $v['totals']['subtotal'],
            '{product_proforma_invoice_total_vat}' => $v['totals']['total_vat'],
            '{contract_sales_notes}' => $v['order']['notes'],
            '{product_proforma_invoice_total_with_vat}' => $v['totals']['grand_total'],
            '{contract_payment_terms}' => $v['order']['payment_terms'],
            '{contract_terms_of_payment}' => $v['order']['payment_terms'],

            '{contract_sales_company_bank_account_name}' => $v['bank']['name'],
            '{contract_sales_company_bank_account_number}' => $v['bank']['ac_no'],
            '{contract_sales_company_bank_account_sort_code}' => $v['bank']['sort_code'],
            '{contract_sales_company_bank_account_iban}' => $v['bank']['iban'],
            '{contract_sales_company_bank_account_swift}' => $v['bank']['swift'],

            '{proforma_invoice_date}' => $v['order']['invoice_date'],
            '{proforma_invoice_number}' => $v['order']['invoice_no'],
            '{current_date}' => $v['order']['invoice_date'],
            '{date}' => $v['order']['invoice_date'],
            '{contract_date}' => $v['order']['invoice_date'],

            '{seller_single_product_name}' => $v['products'][0]['description'] ?? 'Dross',
            '{seller_single_product_baseCodeAnnexIX}' => 'B1100',
            '{seller_single_product_euListOfWastes}' => '11 05 02',

            '{contract_sales_company_name}' => $v['meta']['name'],
            '{contract_sales_company_address_line1}' => $v['meta']['address1'],
            '{contract_sales_company_address_line2}' => $v['meta']['address2'],
            '{contract_sales_company_country}' => $v['meta']['country'],

            '{supplier_name}' => $v['meta']['name'],
            '{supplier_address_line1}' => $v['meta']['address1'],
            '{supplier_address_line2}' => $v['meta']['address2'],
            '{supplier_address_city}' => $v['meta']['city'],
            '{supplier_address_postal_code}' => $v['meta']['postcode'],
            '{supplier_address_state}' => $v['meta']['state'],
            '{supplier_address_country}' => $v['meta']['country'],

            '{user_signature}' => $v['meta']['signature_uri'] ?? '',
            '{company_stamp}' => $v['meta']['stamp_uri'] ?? '',
            '{user_name}' => $v['meta']['user_name'] ?? 'Authorised Signatory',
            '{user_email}' => $v['meta']['user_email'] ?? '',
            '{authorised_person_name}' => $v['meta']['user_name'] ?? 'Authorised Signatory',
            '{approved_by}' => $v['meta']['user_name'] ?? 'Authorised Signatory',

            '[{proforma_invoice:product_proforma_invoice}]' => $proformaProdRows,
            '[{subtemplate:delyvery_note_products}]' => $deliveryProdRows,
            '[{subject_contract_of_sales:subject_contract_of_sales}]' => $salesContractRows,
        ];

        $html = str_replace(array_keys($replacements), array_values($replacements), $rawHtml);

        // Fix img src for logo, footer, stamp and signature if still referencing id
        $html = preg_replace('/<img\s+([^>]*?)id="\{company_url_logo\}"([^>]*?)src=""([^>]*?)>/i', '<img $1id="{company_url_logo}"$2src="' . $v['meta']['logo_uri'] . '"$3>', $html);
        $html = preg_replace('/<img\s+([^>]*?)src=""([^>]*?)id="\{company_url_logo\}"([^>]*?)>/i', '<img $1src="' . $v['meta']['logo_uri'] . '"$2id="{company_url_logo}"$3>', $html);
        $html = preg_replace('/<img\s+([^>]*?)id="\{company_url_footer\}"([^>]*?)src=""([^>]*?)>/i', '<img $1id="{company_url_footer}"$2src="' . $v['meta']['footer_uri'] . '"$3>', $html);
        $html = preg_replace('/<img\s+([^>]*?)src=""([^>]*?)id="\{company_url_footer\}"([^>]*?)>/i', '<img $1src="' . $v['meta']['footer_uri'] . '"$2id="{company_url_footer}"$3>', $html);
        if (!empty($v['meta']['stamp_uri'])) {
            $html = preg_replace('/<img\s+([^>]*?)id="\{company_stamp\}"([^>]*?)src=""([^>]*?)>/i', '<img $1id="{company_stamp}"$2src="' . $v['meta']['stamp_uri'] . '"$3>', $html);
            $html = preg_replace('/<img\s+([^>]*?)src=""([^>]*?)id="\{company_stamp\}"([^>]*?)>/i', '<img $1src="' . $v['meta']['stamp_uri'] . '"$2id="{company_stamp}"$3>', $html);
        }
        if (!empty($v['meta']['signature_uri'])) {
            $html = preg_replace('/<img\s+([^>]*?)id="\{user_signature\}"([^>]*?)src=""([^>]*?)>/i', '<img $1id="{user_signature}"$2src="' . $v['meta']['signature_uri'] . '"$3>', $html);
            $html = preg_replace('/<img\s+([^>]*?)src=""([^>]*?)id="\{user_signature\}"([^>]*?)>/i', '<img $1src="' . $v['meta']['signature_uri'] . '"$2id="{user_signature}"$3>', $html);
        }

        return self::populateBase64inHtml($html);
    }

    /**
     * Exact PROFORMA INVOICE HTML format matching React/Java.
     */
    protected function buildProformaInvoiceHtml(array $v): string
    {
        $logoUri = $v['meta']['logo_uri'];
        $footerUri = $v['meta']['footer_uri'];

        $productRows = '';
        foreach ($v['products'] as $p) {
            $productRows .= '
    <tr style="height:6mm; font-size: 7pt;line-height: 1.25em;">
      <th style="border:1px black solid; padding:1mm; font-weight:900; text-align:center; width:5mm;">
        ' . $p['no'] . '
      </th>
      <th style="border:1px black solid; padding:1mm; font-weight:400; text-align:left; min-width:60mm;">
        ' . htmlspecialchars($p['description']) . '
      </th>
      <th style="border:1px black solid; padding:1mm; font-weight:400; text-align:center;">
        ' . $p['lme_perc'] . '
      </th>
      <th style="border:1px black solid; padding:1mm; font-weight:400; text-align:right;">
        ' . $p['lme_price'] . '
      </th>
      <th style="border:1px black solid; padding:1mm; font-weight:400; text-align:right;">
        ' . $p['premium'] . '
      </th>
      <th style="border:1px black solid; padding:1mm; font-weight:400; text-align:right;">
        ' . $p['quantity'] . '
      </th>
      <th style="border:1px black solid; padding:1mm; font-weight:400; text-align:right;">
        ' . $p['uprice'] . '
      </th>
      <th style="border:1px black solid; padding:1mm; font-weight:400; text-align:right;">
        ' . $p['amount'] . '
      </th>
    </tr>';
        }

        return <<<HTML
<html>
<body vlink="blue" link="blue" style="margin: 0; padding: 0; background-color: #ffffff;">
<div style="position:relative; min-height: 267mm; width: 180mm; font-family: Arial, Helvetica, sans-serif; padding:0px; font-size: 12px; line-height: 1.6em; font-weight: 500; margin: 0 auto; color: #000000;">
  <table border="0" width="100%" cellspacing="0" cellpadding="0">
    <tr>
      <td valign="top" style="width: 65%;">
        <div style="text-align: left">
          <img src="{$logoUri}" id="company_url_logo" alt="Metaenterprises" style="height:110px;" />
        </div>
      </td>
      <td valign="top">
        <div style="text-align: left; font-size: 10px; line-height: 1.25em;">
          <span style="font-size: 16px;"><strong>{$v['meta']['name']}</strong></span><br />
          {$v['meta']['address1']} {$v['meta']['address2']}<br />
          {$v['meta']['postcode']}, {$v['meta']['city']}<br />
          {$v['meta']['state']}, {$v['meta']['country']}<br />
          Phone: {$v['meta']['tel']}<br />
          Email: {$v['meta']['email']}<br />
          Website: {$v['meta']['web']}<br />
          Company No. {$v['meta']['company_number']}<br />
          VAT Reg. No. : {$v['meta']['vat']}<br />
          EORI: {$v['meta']['eori']}<br />
        </div>
      </td>
    </tr>
  </table>
  <br />
  <table border="0" align="center" cellpadding="0" cellspacing="0"
         style="border-collapse:collapse; border:1px solid black; width:100%; font-size:8pt; background-color:white;vertical-align: middle;">
    <caption style="border:1px black solid; border-bottom:0; padding:2mm 1mm; font-size:14pt; font-weight:900; text-align: center; letter-spacing: 0.5px;">
      <div>PROFORMA INVOICE</div>
    </caption>
    <tr>
      <td style="border:1px black solid; border-right:0; padding:1.5mm; width:50%; vertical-align:middle; text-align:center; font-weight:900; height: 6mm;">
        BUYER
      </td>
      <td style="border:1px black solid; padding:1.5mm; vertical-align:middle; text-align:center; font-weight:900;">
        Delivery Terms
      </td>
      <td style="border:1px black solid; padding:1.5mm; vertical-align:middle; text-align:center; font-weight:900;">
        Contract No.
      </td>
    </tr>
    <tr style="font-size: 8pt;line-height: 1.25em;">
      <td rowspan="3" style="border:1px black solid; padding:2mm; width:50%; vertical-align:top;">
        <strong>{$v['buyer']['name']}</strong><br />
        {$v['buyer']['address1']}<br />
        {$v['buyer']['postcode']}<br />
        {$v['buyer']['city']}<br />
        {$v['buyer']['state']}<br />
        {$v['buyer']['country']}<br />
      </td>
      <td style="padding:1.5mm; text-align:center; vertical-align:top;width: 25%;height: 9mm;">
        {$v['order']['delivery_terms']}
      </td>
      <td style="border:1px black solid; border-right:0; border-bottom:0; padding:1.5mm; text-align:center; vertical-align:top;">
        {$v['order']['code']}
      </td>
    </tr>
    <tr>
      <td style="border:1px black solid; padding:1.5mm; vertical-align:top; text-align:center; font-weight:900;height:6mm;">
        Customer Ref No.
      </td>
      <td style="border:1px black solid; padding:1.5mm; vertical-align:top; text-align:center; font-weight:900;">
        Mode of Transport
      </td>
    </tr>
    <tr style="font-size: 8pt;line-height: 1.25em;">
      <td style="padding:1.5mm; text-align:center; vertical-align:top; height: 9mm;">
        {$v['order']['customer_ref']}
      </td>
      <td style="border:1px black solid; border-right:0; border-bottom:0; padding:1.5mm; text-align:center; vertical-align:top;">
        {$v['order']['mode_of_transport']}
      </td>
    </tr>
  </table>

  <table border="0" align="center" cellpadding="0" cellspacing="0"
         style="border-top: 0px; border-collapse:collapse; width:100%; font-size:8pt; background-color:white;">
    <tr style="height:6mm">
      <th style="border:1px black solid; padding:1.5mm; font-weight:900; text-align:center; width:16%;">
        Offer No.
      </th>
      <th style="border:1px black solid; padding:1.5mm; font-weight:900; text-align:center; width:16%;">
        Invoice No
      </th>
      <th style="border:1px black solid; padding:1.5mm; font-weight:900; text-align:center;width:16%">
        Invoice Date
      </th>
      <th style="border:1px black solid; padding:1.5mm; font-weight:900; text-align:center; width:26%;">
        Country of Origin
      </th>
      <th style="border:1px black solid; padding:1.5mm; font-weight:900; text-align:center;width:26%">
        Country of Delivery
      </th>
    </tr>
    <tr style="height:6mm;">
      <td style="border:1px black solid; padding:1.5mm; text-align:center; vertical-align:top;">
        {$v['order']['offer_no']}
      </td>
      <td style="border:1px black solid; padding:1.5mm; text-align:center; vertical-align:top;">
        {$v['order']['invoice_no']}
      </td>
      <td style="border:1px black solid; padding:1.5mm; text-align:center; vertical-align:top;">
        {$v['order']['invoice_date']}
      </td>
      <td style="border:1px black solid; padding:1.5mm; text-align:center; vertical-align:top;">
        {$v['order']['country_origin']}
      </td>
      <td style="border:1px black solid; padding:1.5mm; text-align:center; vertical-align:top;">
        {$v['order']['country_delivery']}
      </td>
    </tr>
  </table>

  <table border="0" align="center" cellpadding="0" cellspacing="0"
         style="border-collapse:collapse; width:100%; font-size:8pt; background-color:white;">
    <tr style="height:6mm; max-height: 6mm; line-height: 1.20em;">
      <th style="border:1px black solid; text-align:center;width:5mm">
        No.
      </th>
      <th style="border:1px black solid; padding:1mm; font-weight:900; text-align:center;min-width:60mm">
        Product Description
      </th>
      <th style="border:1px black solid; padding:1mm; font-weight:900; text-align:center;width:10mm">
        LME%
      </th>
      <th style="border:1px black solid; padding:1mm; font-weight:900; text-align:center;width:15mm">
        LME Price
      </th>
      <th style="border:1px black solid; padding:1mm; font-weight:900; text-align:center;width:15mm">
        Premium
      </th>
      <th style="border:1px black solid; padding:1mm; font-weight:900; text-align:center;width:10mm">
        Quantity
      </th>
      <th style="border:1px black solid; padding:1mm; font-weight:900; text-align:center;width:20mm;">
        U. Price
      </th>
      <th style="border:1px black solid; padding:1mm; font-weight:900; text-align:center; width:23mm;">
        Amount
      </th>
    </tr>
    {$productRows}
    <tr style="height:6mm;font-size:7pt;">
      <td colspan="2" style="border:1px black solid;">
        <table style="border-collapse:collapse; width:100%; border: 0px; height: 8mm;">
          <tr>
            <td style="font-size:7pt;width: 20mm;padding:1mm; font-weight:900;">VAT Rate</td>
            <td style="font-size:7pt;padding:1mm; border-left: 1px solid black;">{$v['totals']['vat_rate']}</td>
          </tr>
        </table>
      </td>
      <td colspan="4" style="border:1px black solid;text-align: center;font-weight:900;"></td>
      <td style="border:1px black solid; padding:1mm; font-weight:900; text-align:center; vertical-align:top;">
        Sub Total
      </td>
      <td style="border:1px black solid; padding:1mm; text-align:right; vertical-align:top; width:29mm;">
        {$v['totals']['subtotal']}
      </td>
    </tr>
    <tr style="height:6mm; border-left:1px black solid;font-size:7pt;">
      <td colspan="6" style="border:0px;border-left:1px black solid;padding:1mm"><b>NOTES:</b></td>
      <td style="border:1px black solid; padding:1mm; font-weight:900; text-align:center; vertical-align:top;">
        Total VAT
      </td>
      <td style="border:1px black solid; padding:1mm; text-align:right; vertical-align:top; width:29mm;">
        {$v['totals']['total_vat']}
      </td>
    </tr>
    <tr style="height:6mm; border-left:1px black solid;font-size:7pt; ">
      <td rowspan="2" colspan="6" style="border:0px;border-left: 1px solid black;vertical-align: top;">
        <div style="padding:1mm; font-size: 8pt; white-space: pre-line;line-height: 1.5em;">{$v['order']['notes']}</div>
      </td>
      <td style="border:1px black solid; padding:1mm; font-weight:900; text-align:center; vertical-align:top;">
        Total
      </td>
      <td style="border:1px black solid; padding:1mm; text-align:right; vertical-align:top; width:29mm;">
        {$v['totals']['grand_total']}
      </td>
    </tr>
    <tr>
      <td colspan="2" style="min-height:50mm;border-right:1px solid #000;">
        <div style="min-height:10mm"></div>
      </td>
    </tr>

    <tr style="min-height:6mm">
      <td colspan="8" style="border:1px solid #000; text-align: center; padding: 1.5mm; font-weight: bold;">
        {$v['order']['payment_terms']}
      </td>
    </tr>
    <tr style="min-height:15mm">
      <td colspan="8" style="border:1px solid #000; padding:2mm; text-align:center; line-height:1.35em;">
        Bank Name: {$v['bank']['name']}<br />
        AC/NO: {$v['bank']['ac_no']}<br />
        SORT CODE: {$v['bank']['sort_code']}<br />
        IBAN: {$v['bank']['iban']}<br />
        SWIFT (BIC): {$v['bank']['swift']}<br />
      </td>
    </tr>
    <tr style="min-height:15mm">
      <td colspan="8" style="border:1px solid #000; padding:2mm; text-align:left;font-weight: 900; font-size: 6.5pt; line-height:1.25em;">
        For EU only: "VAT: EC invoice simplified under Articles 128-132 of the PPA Act" or "VAT: EC invoice simplified under Article 141 of Directive 2006/112 / EC"
      </td>
    </tr>
  </table>

  <div style="margin-top: 20px; text-align:center; width: 100%;">
    <img src="{$footerUri}" id="company_url_footer" alt="rodape" style="width:100%;text-align: center;" />
  </div>
</div>
</body>
</html>
HTML;
    }

    /**
     * Exact DELIVERY NOTE HTML format matching React/Java.
     */
    protected function buildDeliveryNoteHtml(array $v): string
    {
        $logoUri = $v['meta']['logo_uri'];
        $footerUri = $v['meta']['footer_uri'];

        $productRows = '';
        foreach ($v['products'] as $p) {
            $productRows .= '
            <tr style="border:1px solid black">
                <td style="text-align:center; border:1px solid black; padding: 2mm;">
                    ' . $p['no'] . '
                </td>
                <td style="text-align:center; border:1px solid black; padding: 2mm;">
                    ' . htmlspecialchars($p['description']) . '
                </td>
                <td style="text-align:center; border:1px solid black; padding: 2mm;">
                    ' . $p['quantity'] . ' MT
                </td>
            </tr>';
        }

        return <<<HTML
<html lang="en-GB">
<head>
    <meta charset="UTF-8">
    <title>Delivery Note - {$v['order']['code']}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #ffffff;">
    <div style="position:relative; min-height: 267mm; width: 180mm; font-family: Arial, Helvetica, sans-serif; padding:0px; font-size: 12px; line-height: 1.6em; font-weight: 500; margin: 0 auto; color: #000000;">
        <table style="width: 100%; border-spacing: 0; border-collapse: collapse;">
            <tr>
                <td style="vertical-align: top; width: 65%;">
                    <div style="text-align: left">
                        <img src="{$logoUri}" id="company_url_logo" alt="Metaenterprises" style="height:110px;" />
                    </div>
                </td>
                <td style="vertical-align: top;">
                    <div style="text-align: left; font-size: 10px; line-height: 1.25em;">
                        <span style="font-size: 16px;"><strong>{$v['meta']['name']}</strong></span><br />
                        {$v['meta']['address1']} {$v['meta']['address2']}<br />
                        {$v['meta']['postcode']}, {$v['meta']['city']}<br />
                        {$v['meta']['state']}, {$v['meta']['country']}<br />
                        Phone: {$v['meta']['tel']}<br />
                        Email: {$v['meta']['email']}<br />
                        Website: {$v['meta']['web']}<br />
                        Company No. {$v['meta']['company_number']}<br />
                        VAT Reg. No. : {$v['meta']['vat']}<br />
                        EORI: {$v['meta']['eori']}<br />
                    </div>
                </td>
            </tr>
        </table>

        <div style="margin: 15px 0;">
            <h2 style="text-align: center; font-size: 20px; font-weight: bold; margin: 0;">Delivery Note</h2>
        </div>

        <div style="width: 100%; padding-bottom: 25px;">
            <table style="width: 100%; font-size: 13px;">
                <tr>
                    <td style="text-align: left; vertical-align: top; font-weight: bold; width: 15%;">
                        Buyer:
                    </td>
                    <td style="text-align: left; vertical-align: top; font-weight: bold; width: 45%;">
                        {$v['buyer']['name']}<br />
                        {$v['buyer']['address1']}<br />
                        {$v['buyer']['postcode']} {$v['buyer']['city']}<br />
                        {$v['buyer']['country']}
                    </td>
                    <td style="text-align: left; vertical-align: top; font-weight: bold; width: 15%;">
                        Date:<br />
                        Contract:<br />
                        Invoice:
                    </td>
                    <td style="text-align: left; vertical-align: top; font-weight: bold; width: 25%;">
                        {$v['order']['invoice_date']}<br />
                        {$v['order']['code']}<br />
                        {$v['order']['invoice_no']}
                    </td>
                </tr>
            </table>
        </div>

        <table style="width:100%;text-align:center; border-collapse:collapse; margin-bottom: 30px;">
            <tr style="background-color: #f8fafc;">
                <td style="width:10%;text-align:center; font-weight: bold; font-size: 13px; border:2px solid black; padding: 2mm;">
                    <b>No.</b>
                </td>
                <td style="width:65%;text-align:center; font-weight: bold; font-size: 13px; border:2px solid black; padding: 2mm;">
                    <b>Description</b>
                </td>
                <td style="width:25%;text-align:center; font-weight: bold; font-size: 13px; border:2px solid black; padding: 2mm;">
                    <b>Quantity</b>
                </td>
            </tr>
            {$productRows}
        </table>

        <div style="padding-top: 30px; margin-bottom: 30px;">
            <table style="width: 100%; border-spacing: 0; border-collapse: collapse;">
                <tr>
                    <td style="vertical-align: top; font-weight: bold; font-size: 13px; width: 50%;">
                        Date: {$v['order']['invoice_date']}
                    </td>
                    <td style="vertical-align: top; font-weight: bold; font-size: 13px; width: 50%;">
                        Date:
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: top; padding-top: 15px; height: 35px;">
                        Seller Signature:
                    </td>
                    <td style="vertical-align: top; padding-top: 15px; height: 35px;">
                        Buyer Signature:
                    </td>
                </tr>
            </table>
        </div>

        <div style="margin-top: 30px; text-align:center; width: 100%;">
            <img src="{$footerUri}" id="company_url_footer" alt="rodape" style="width:100%;text-align: center;" />
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Exact CONTRACT OF SALES HTML format matching React/Java.
     */
    protected function buildContractOfSalesHtml(array $v): string
    {
        $logoUri = $v['meta']['logo_uri'];
        $footerUri = $v['meta']['footer_uri'];

        $productRows = '';
        foreach ($v['products'] as $p) {
            $productRows .= '
            <tr style="height:6mm; line-height: 1.2em;">
                <th style="border:1px black solid; padding:1.5mm; text-align:center; width:15mm; vertical-align: top;">
                    ' . $p['no'] . '
                </th>
                <th style="border:1px black solid; padding:1.5mm; text-align:left; vertical-align: top;">
                    ' . htmlspecialchars($p['description']) . '
                </th>
                <th style="border:1px black solid; padding:1.5mm; text-align:center; width:22mm; vertical-align: top;">
                    ' . $p['lme_price'] . '
                </th>
                <th style="border:1px black solid; padding:1.5mm; text-align:center; width:22mm; vertical-align: top;">
                    ' . $p['premium'] . '
                </th>
                <th style="border:1px black solid; padding:1.5mm; text-align:center; width:22mm; vertical-align: top;">
                    ' . $p['uprice'] . '
                </th>
                <th style="border:1px black solid; padding:1.5mm; text-align:center; width:22mm; vertical-align: top;">
                    ' . $p['quantity'] . '
                </th>
                <th style="border:1px black solid; padding:1.5mm; text-align:center; width:26mm; vertical-align: top;">
                    ' . $p['amount'] . '
                </th>
            </tr>';
        }

        return <<<HTML
<html>
<head>
    <meta charset="UTF-8">
    <title>Contract of Sales - {$v['order']['code']}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #ffffff;">
<div style="position:relative; min-height: 267mm; width: 180mm; font-family: Arial, Helvetica, sans-serif; padding:0px; font-size: 11px; line-height: 1.5em; font-weight: 500; margin: 0 auto; color: #000000;">
    <table border="0" width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td valign="top" style="width: 65%;">
                <div style="text-align: left">
                    <img src="{$logoUri}" id="company_url_logo" alt="Metaenterprises" style="height:110px;" />
                </div>
            </td>
            <td valign="top">
                <div style="text-align: left; font-size: 10px; line-height: 1.25em;">
                    <span style="font-size: 15px;"><strong>{$v['meta']['name']}</strong></span><br />
                    {$v['meta']['address1']} {$v['meta']['address2']}<br />
                    {$v['meta']['postcode']}, {$v['meta']['city']}<br />
                    {$v['meta']['state']}, {$v['meta']['country']}<br />
                    Phone: {$v['meta']['tel']}<br />
                    Email: {$v['meta']['email']}<br />
                    Website: {$v['meta']['web']}<br />
                    Company No. {$v['meta']['company_number']}<br />
                    VAT Reg. No. : {$v['meta']['vat']}<br />
                    EORI: {$v['meta']['eori']}<br />
                </div>
            </td>
        </tr>
    </table>
    <br />
    <div>
        <h1 id="title" style="text-align:center; font-weight:900; font-size: 20px; margin: 5px 0;">Contract of Sales</h1>
    </div>
    <div style="display: flex; margin-bottom: 5px;">
        <table style="width: 100%; text-align: left; font-size: 12px; font-weight: bold;">
            <tr>
                <td style="width: 50%">
                    Contract Number: {$v['order']['code']}
                </td>
                <td style="width: 50%; text-align: right;">
                    Date: {$v['order']['invoice_date']}
                </td>
            </tr>
        </table>
    </div>
    <table border="0" align="center" cellpadding="0" cellspacing="0"
           style="border-collapse:collapse; width:100%; font-size:8.5pt; background-color:white;">
        <tr>
            <td style="border:1px black solid; padding:2.5mm; width:50%; vertical-align:top;">
                <span style="font-weight: 900">BUYER</span><br />
                <strong>{$v['buyer']['name']}</strong><br />
                {$v['buyer']['address1']}<br />
                {$v['buyer']['postcode']} {$v['buyer']['city']}<br />
                {$v['buyer']['state']}, {$v['buyer']['country']}<br />
                VAT Number: {$v['buyer']['vat']}<br />
                <p style="margin: 4px 0 0 0;"><strong>Consignee:</strong> As Buyer</p>
            </td>
            <td style="border:1px black solid; vertical-align: top;">
                <table border="0" align="center" cellpadding="0" cellspacing="0"
                       style="border-collapse:collapse; width:100%; font-size:8.5pt; background-color:white;">
                    <tr>
                        <td style="border:1px black solid; padding:1.5mm; font-weight:900; text-align:center; width:50%;">
                            Delivery Terms
                        </td>
                        <td style="border:1px black solid; padding:1.5mm; font-weight: 900; text-align:center; width:50%;">
                            Terms of Payment
                        </td>
                    </tr>
                    <tr>
                        <td style="border:1px black solid; padding:2mm; text-align:center;">
                            {$v['order']['delivery_terms']}
                        </td>
                        <td style="border:1px black solid; padding:2mm; text-align:center;">
                            {$v['order']['payment_terms']}
                        </td>
                    </tr>
                    <tr>
                        <td style="border:1px black solid; font-weight: 900; padding:1.5mm; text-align:center;">
                            Mode of Transport
                        </td>
                        <td style="border:1px black solid; font-weight: 900; padding:1.5mm; text-align:center;">
                            Currency
                        </td>
                    </tr>
                    <tr>
                        <td style="border:1px black solid; padding:2mm; text-align:center;">
                            {$v['order']['mode_of_transport']}
                        </td>
                        <td style="border:1px black solid; padding:2mm; text-align:center;">
                            {$v['order']['currency']}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div style="margin-top: 10px;">
        <p style="font-weight: bold; margin: 4px 0;">1. Subject matter of contract & Products:</p>
        <table border="0" align="center" cellpadding="0" cellspacing="0"
               style="border-collapse:collapse; width:100%; font-size:8pt; background-color:white;">
            <tr style="height:6mm; background-color: #f1f5f9;">
                <th style="border:1px black solid; padding:1mm; text-align:center; width:15mm;">No.</th>
                <th style="border:1px black solid; padding:1mm; text-align:left;">Description</th>
                <th style="border:1px black solid; padding:1mm; text-align:center; width:22mm;">Price</th>
                <th style="border:1px black solid; padding:1mm; text-align:center; width:22mm;">Premium</th>
                <th style="border:1px black solid; padding:1mm; text-align:center; width:22mm;">U.Price</th>
                <th style="border:1px black solid; padding:1mm; text-align:center; width:22mm;">Quantity</th>
                <th style="border:1px black solid; padding:1mm; text-align:center; width:26mm;">Amount</th>
            </tr>
            {$productRows}
            <tr>
                <td colspan="6" style="border:1px black solid; padding:1.5mm; text-align:right; font-weight:bold;">Total Amount:</td>
                <td style="border:1px black solid; padding:1.5mm; text-align:center; font-weight:bold;">{$v['totals']['grand_total']}</td>
            </tr>
        </table>
    </div>

    <div style="margin-top: 15px; font-size: 8pt; line-height: 1.4em;">
        <p style="margin: 2px 0;"><strong>2. Title and Risk:</strong> Risk of loss shall pass to the Buyer in accordance with the specified Incoterm. Title shall remain with the Seller until full payment is received.</p>
        <p style="margin: 2px 0;"><strong>3. Governing Law:</strong> This Contract shall be governed by and construed in accordance with English Law.</p>
    </div>

    <div style="margin-top: 25px;">
        <table style="width: 100%; border-spacing: 0; border-collapse: collapse; font-size: 9pt;">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <strong>For Seller: {$v['meta']['name']}</strong><br /><br />
                    Authorized Signature: _______________________
                </td>
                <td style="width: 50%; vertical-align: top; text-align: right;">
                    <strong>For Buyer: {$v['buyer']['name']}</strong><br /><br />
                    Accepted Signature: _______________________
                </td>
            </tr>
        </table>
    </div>

    <div style="margin-top: 30px; text-align:center; width: 100%;">
        <img src="{$footerUri}" id="company_url_footer" alt="rodape" style="width:100%;text-align: center;" />
    </div>
</div>
</body>
</html>
HTML;
    }

    /**
     * Exact SPECIMEN CONTRACT HTML format matching React/Java.
     */
    protected function buildSpecimenContractHtml(array $v): string
    {
        $logoUri = $v['meta']['logo_uri'];
        $footerUri = $v['meta']['footer_uri'];

        return <<<HTML
<div style="position: relative; min-height: 267mm; width: 180mm; font-family: Arial, Helvetica, sans-serif; padding: 0px; font-size: 11px; line-height: 1.5em; font-weight: 500; margin: 0 auto; color: #000000; background: #ffffff;">
<table border="0" width="100%" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td style="width: 65%;" valign="top">
<div style="text-align: left;"><img style="height: 110px;" src="{$logoUri}" id="company_url_logo" alt="logotipo"></div>
</td>
<td valign="top">
<div style="text-align: left; font-size: 10px; line-height: 1.25em;"><span style="font-size: 15px;"><strong>{$v['meta']['name']}</strong></span><br>{$v['meta']['address1']} {$v['meta']['address2']}<br>{$v['meta']['postcode']}, {$v['meta']['city']}<br>{$v['meta']['state']}, {$v['meta']['country']}<br>Phone: {$v['meta']['tel']}<br>Email: {$v['meta']['email']}<br>Website: {$v['meta']['web']}<br>Company No. {$v['meta']['company_number']}<br>VAT Reg. No. : {$v['meta']['vat']}<br>EORI: {$v['meta']['eori']}</div>
</td>
</tr>
</tbody>
</table>
<div style="display: flex; margin: 10px 0;">
<div style="width: 100%;">
<h2 style="text-align: center; font-weight: 900; font-size: 18px; margin: 0;">SPECIMEN CONTRACT</h2>
</div>
</div>
<p style="font-size: 9pt;">In accordance with <span style="font-weight: 900;">Article 18 Para. 2 of the Regulation (EC) Nr. 1013/2006</span> of the European Parliament and Council of 14 June 2006 on the Shipment of Waste</p>
<div style="font-weight: 900; margin-top: 5px;">Between</div>
<div style="font-size: 9pt; margin-bottom: 5px;">{$v['meta']['name']}<br>{$v['meta']['address1']} {$v['meta']['address2']}<br>{$v['meta']['country']}<br><em>Hereinafter called the person initiating the shipment</em></div>
<p style="margin: 3px 0;">and</p>
<div style="font-size: 9pt; margin-bottom: 8px;"><strong>{$v['buyer']['name']}</strong><br>{$v['buyer']['address1']}<br>{$v['buyer']['country']}<br>VAT: {$v['buyer']['vat']}<br><em>Hereinafter called the consignee.</em></div>
<div style="margin-bottom: 6px; font-size: 9pt;"><strong>The Subject of this contract is the transboundary shipment of waste for purposes of recovery</strong></div>
<div style="font-weight: 900; font-size: 9pt;">Contract Number: <span style="font-weight: normal;">{$v['order']['code']}</span>&nbsp;&nbsp;&nbsp;Date: <span style="font-weight: normal;">{$v['order']['invoice_date']}</span></div>
<div style="font-weight: 900; font-size: 9pt;">Description of waste: <span style="font-weight: normal;">{$v['products'][0]['description']}</span></div>
<div style="font-weight: 900; font-size: 9pt;">AND Basel Code Annex IX: <span style="font-weight: normal;">B1100</span></div>
<div style="font-weight: 900; font-size: 9pt;">EU List of Wastes: <span style="font-weight: normal;">11 05 02</span></div>
<div style="margin-top: 8px; font-weight: bold; font-size: 9pt;">This contract encompasses the obligation:</div>
<div style="margin-top: 4px; line-height: 1.3em; font-size: 8.5pt;">1) Of the person initiating the shipment - to take back the waste or to secure its recovery in a different manner and if necessary to ensure storage of it on a temporary basis if the shipment or recovery of the waste cannot be completed as planned or if it was carried out as an illegal shipment.</div>
<div style="margin-top: 4px; line-height: 1.3em; font-size: 8.5pt;">2) Of the receiving party - insofar as the person initiating the shipment is not in a position when carrying out the shipment or recovery (e.g. because of insolvency) to take back the waste or to secure its recovery in a different manner, to ensure storage of it on a temporary basis if necessary.</div>
<div style="margin-top: 6px; font-size: 8.5pt;">A copy of the contract is to be given to the responsible authorities if they should request it.</div>
<table border="0" width="100%" cellspacing="0" cellpadding="0" style="margin-top: 20px;">
<tbody>
<tr>
<td style="width: 50%;" valign="top">
<p style="margin-bottom: 2mm;"><span style="font-weight: bold;">Date:</span> {$v['order']['invoice_date']}</p>
<div style="height: 25px;"></div>
<p style="margin-top: 1mm; font-weight: bold; font-size: 9.5pt;">Person initiating the shipment<br><small>{$v['meta']['name']}</small></p>
</td>
<td valign="top">
<p style="margin-bottom: 2mm; font-weight: bold;">Date: {$v['order']['invoice_date']}</p>
<div style="height: 25px;"></div>
<p style="margin-top: 1mm; font-weight: bold; font-size: 9.5pt;">Consignee<br><small>{$v['buyer']['name']}</small></p>
</td>
</tr>
</tbody>
</table>
<div style="margin-top: 30px; text-align:center; width: 100%;">
<img style="width: 100%; text-align: center;" src="{$footerUri}" id="company_url_footer" alt="rodape">
</div>
</div>
HTML;
    }

    /**
     * Exact EXPORT LIABILITY HTML format matching React/Java.
     */
    protected function buildExportLiabilityHtml(array $v): string
    {
        $logoUri = $v['meta']['logo_uri'];
        $footerUri = $v['meta']['footer_uri'];

        return <<<HTML
<!DOCTYPE html>
<html>
<body style="margin: 0; padding: 0; background-color: #ffffff;">
<div style="position:relative; min-height: 267mm; width: 180mm; font-family: Arial, Helvetica, sans-serif; padding:0px; font-size: 12px; line-height: 1.6em; font-weight: 500; margin: 0 auto; color: #000000;">
    <table border="0" width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td valign="top" style="width: 65%;">
                <div style="text-align: left"><img src="{$logoUri}" id="company_url_logo" alt="Metaenterprises" style="height:110px;" /></div>
            </td>
            <td valign="top">
                <div style="text-align: left; font-size: 10px; line-height: 1.25em;">
                    <span style="font-size: 16px;"><strong>{$v['meta']['name']}</strong></span><br />
                    {$v['meta']['address1']} {$v['meta']['address2']}<br />
                    {$v['meta']['postcode']}, {$v['meta']['city']}<br />
                    {$v['meta']['state']}, {$v['meta']['country']}<br />
                    Phone: {$v['meta']['tel']}<br />
                    Email: {$v['meta']['email']}<br />
                    Website: {$v['meta']['web']}<br />
                    Company No. {$v['meta']['company_number']}<br />
                    VAT Reg. No. : {$v['meta']['vat']}<br />
                    EORI: {$v['meta']['eori']}<br />
                </div>
            </td>
        </tr>
    </table>

    <div style="margin-top: 15px;">
        <table border="0" width="100%" cellspacing="0" cellpadding="5">
            <tr>
                <td style="text-align: right; font-size: 14px; font-weight: bold;">
                    Date: {$v['order']['invoice_date']}
                </td>
            </tr>
        </table>
    </div>

    <div style="background-color: #ffffff; padding: 10px; text-align: center;">
        <div style="font-size: 22px; font-weight: bold; margin-top: 25px; margin-bottom: 20px;">
            Export Liability Declaration
        </div>
    </div>

    <div style="padding: 10px 0; text-align: left; font-size: 14px; line-height: 1.8em;">
        <div style="margin-bottom: 30px;">
            <strong>THE LEGAL REPRESENTATIVE OF {$v['meta']['name']} DECLARES UNDER HIS/HER CIVIL AND PENAL LIABILITY THAT:</strong>
        </div>
        <div style="margin-bottom: 25px;">
            THE MATERIAL MANUFACTURER IS:<br />
            <strong>{$v['meta']['name']}</strong><br />
            {$v['meta']['address1']}<br />
            {$v['meta']['city']}, {$v['meta']['postcode']}, {$v['meta']['country']}
        </div>
        <div style="margin-bottom: 25px;">
            THE COUNTRY OF ORIGIN OF THE WASTE IS:<br />
            <strong>{$v['order']['country_origin']}</strong>
        </div>
        <div style="margin-bottom: 30px;">
            {$v['meta']['name']} ASSUMES RESPONSIBILITY FOR ALL INFORMATION COMMUNICATED TO <strong>{$v['buyer']['name']}</strong> WITH REGARDS TO THE SHIPPING SUBJECT OF CONTRACT <strong>{$v['order']['code']}</strong>.
        </div>
    </div>

    <div style="line-height: 1.4em; font-size: 13px; margin-top: 40px;">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%;">
                    Authorized Signature:<br /><br /><br />
                    <strong>Director / Legal Representative</strong><br />
                    {$v['meta']['name']}
                </td>
                <td style="width: 50%; text-align: right;">
                    Official Stamp:<br /><br /><br />
                    Date: {$v['order']['invoice_date']}
                </td>
            </tr>
        </table>
    </div>

    <div style="margin-top: 40px; text-align:center; width: 100%;">
        <img src="{$footerUri}" id="company_url_footer" alt="rodape" style="width:100%;text-align: center;" />
    </div>
</div>
</body>
</html>
HTML;
    }

    /**
     * Exact PURCHASE ORDER HTML format matching React/Java.
     */
    protected function buildPurchaseOrderHtml(array $v): string
    {
        $logoUri = $v['meta']['logo_uri'];
        $footerUri = $v['meta']['footer_uri'];

        return <<<HTML
<html>
<body style="margin: 0; padding: 0; background-color: #ffffff;">
<div style="position:relative; min-height: 267mm; width: 180mm; font-family: Arial, Helvetica, sans-serif; padding:0px; font-size: 12px; line-height: 1.6em; font-weight: 500; margin: 0 auto; color: #000000;">
    <table border="0" width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td valign="top" style="width: 65%;">
                <div style="text-align: left"><img src="{$logoUri}" id="company_url_logo" alt="Metaenterprises" style="height:110px;" /></div>
            </td>
            <td valign="top">
                <div style="text-align: left; font-size: 10px; line-height: 1.25em;">
                    <span style="font-size: 16px;"><strong>{$v['meta']['name']}</strong></span><br />
                    {$v['meta']['address1']} {$v['meta']['address2']}<br />
                    {$v['meta']['postcode']}, {$v['meta']['city']}<br />
                    {$v['meta']['state']}, {$v['meta']['country']}<br />
                    Phone: {$v['meta']['tel']}<br />
                    Email: {$v['meta']['email']}<br />
                    Website: {$v['meta']['web']}<br />
                    Company No. {$v['meta']['company_number']}<br />
                    VAT Reg. No. : {$v['meta']['vat']}<br />
                    EORI: {$v['meta']['eori']}<br />
                </div>
            </td>
        </tr>
    </table>

    <div>
        <h2 style="text-align: center; font-size: 22px; font-weight: bold; margin: 15px 0;">Purchase Order</h2>
    </div>

    <div style="border-bottom: #999 solid 1px; margin: 5px 0px"></div>

    <table style="font-size: 11px; width:100%;line-height: 1.3em;vertical-align: top;">
        <tbody style="vertical-align: top;">
            <tr>
                <td rowspan="4" width="50%">
                    <b>{$v['buyer']['name']}</b><br>
                    {$v['buyer']['address1']}<br>
                    {$v['buyer']['city']}, {$v['buyer']['postcode']}<br>
                    {$v['buyer']['country']}<br>
                </td>
                <td width="70px"><b>Date:</b></td>
                <td>{$v['order']['invoice_date']}</td>
            </tr>
            <tr>
                <td><b>Our Ref:</b></td>
                <td>{$v['order']['code']}</td>
            </tr>
            <tr>
                <td><b>Attn to:</b></td>
                <td>Purchasing Department</td>
            </tr>
            <tr>
                <td><b>Terms:</b></td>
                <td>{$v['order']['delivery_terms']}</td>
            </tr>
        </tbody>
    </table>

    <div style="border-bottom: #999 solid 1px; margin: 10px 0px"></div>

    Dear Partners,<br>
    We confirm herewith having bought from you, in accordance with our general purchasing terms, which are given below:
    <div style="border-bottom: #999 solid 1px; margin: 8px 0px"></div>

    <table width="100%" style="font-size: 11px; line-height: 1.3em; margin: 10px 0;">
        <tr>
            <td style="width:30mm;"><b>Product:</b></td>
            <td>{$v['products'][0]['description']}</td>
        </tr>
        <tr>
            <td><b>Quantity:</b></td>
            <td>{$v['products'][0]['quantity']} MT</td>
        </tr>
        <tr>
            <td><b>Total Amount:</b></td>
            <td><b>{$v['totals']['grand_total']} (EX VAT)</b></td>
        </tr>
        <tr>
            <td><b>Delivery Terms:</b></td>
            <td>{$v['order']['delivery_terms']}</td>
        </tr>
        <tr>
            <td><b>Payment terms:</b></td>
            <td>{$v['order']['payment_terms']}</td>
        </tr>
    </table>

    <ul style="font-size: 10px; line-height: 1.4em; padding-left: 20px;">
        <li>The material must be free from impurities such as wood, plastics, cotton waste, asbestos or asbestos containing products.</li>
        <li>The material should be free of radioactive products or explosives.</li>
        <li>Weight and quality analysis shall be carried out with the partners of {$v['meta']['name']}.</li>
    </ul>

    <div style="margin-top: 30px;">
        <table style="width: 100%; font-size: 11px;">
            <tr>
                <td style="width: 50%;">
                    Authorized Signature<br /><br />
                    For and on behalf of {$v['meta']['name']}
                </td>
                <td style="width: 50%; text-align: right;">
                    Accepted & Confirmed<br /><br />
                    For and on behalf of {$v['buyer']['name']}
                </td>
            </tr>
        </table>
    </div>

    <div style="margin-top: 35px; text-align:center; width: 100%;">
        <img src="{$footerUri}" id="company_url_footer" alt="rodape" style="width:100%;text-align: center;" />
    </div>
</div>
</body>
</html>
HTML;
    }

    /**
     * Exact COMMERCIAL INVOICE HTML format matching React/Java.
     */
    protected function buildCommercialInvoiceHtml(array $v): string
    {
        return $this->buildProformaInvoiceHtml($v); // Uses identical invoice grid with title COMMERCIAL INVOICE
    }

    /**
     * Exact PACKING LIST HTML format matching React/Java.
     */
    protected function buildPackingListHtml(array $v): string
    {
        return $this->buildDeliveryNoteHtml($v);
    }

    /**
     * Extract invoice total amount from HTML.
     */
    public static function extractInvoiceTotalFromHtml(?string $html): ?float
    {
        if (empty($html)) {
            return null;
        }

        // 1. Look for Total or Grand Total row in table
        if (preg_match_all('/(?:Total\s*(?:<br\s*\/?>)?|Grand Total)\s*<\/td>\s*<td[^>]*>\s*([^<]+)<\/td>/i', $html, $matches)) {
            $last = trim(end($matches[1]));
            $clean = preg_replace('/[^0-9.]/', '', str_replace(',', '', $last));
            if (is_numeric($clean)) {
                return (float)$clean;
            }
        }

        // 2. Fallback to Sub Total if Total is absent
        if (preg_match('/Sub\s*Total\s*(?:<br\s*\/?>)?\s*<\/td>\s*<td[^>]*>\s*([^<]+)<\/td>/i', $html, $m)) {
            $clean = preg_replace('/[^0-9.]/', '', str_replace(',', '', $m[1]));
            if (is_numeric($clean)) {
                return (float)$clean;
            }
        }

        return null;
    }

    /**
     * Compare Proforma Invoice and Commercial Invoice totals for a contract
     * and calculate any difference for Credit/Debit Note automation.
     */
    public function getInvoiceDifference(Contract $contract): array
    {
        $defaultResult = [
            'has_difference' => false,
            'type' => null, // 'CREDIT' or 'DEBIT'
            'amount' => 0.0,
            'formatted_amount' => '0.00',
            'currency' => '$',
            'proforma_total' => null,
            'commercial_total' => null,
            'diff' => 0.0,
            'proforma_file_id' => null,
            'commercial_file_id' => null,
            'already_has_note' => false,
            'existing_note_id' => null,
            'existing_file_id' => null,
            'reason' => '',
        ];

        try {
            $files = $contract->generatedFiles ?? $contract->generatedFiles()->get();
            $proforma = $files->first(function ($f) {
                return str_starts_with($f->name, 'PROFORMA_INVOICE_') ||
                       (isset($f->template) && $f->template->type === 'PROFORMA_INVOICE');
            });
            $commercial = $files->first(function ($f) {
                return str_starts_with($f->name, 'COMMERCIAL_INVOICE_') ||
                       (isset($f->template) && $f->template->type === 'COMMERCIAL_INVOICE');
            });

            if (!$proforma || !$commercial) {
                return $defaultResult;
            }

            $htmlProforma = $proforma->html ?: (empty($proforma->content) ? '' : base64_decode($proforma->content));
            $htmlCommercial = $commercial->html ?: (empty($commercial->content) ? '' : base64_decode($commercial->content));

            $totalProforma = self::extractInvoiceTotalFromHtml($htmlProforma);
            $totalCommercial = self::extractInvoiceTotalFromHtml($htmlCommercial);

            if ($totalProforma === null || $totalCommercial === null) {
                return $defaultResult;
            }

            $diff = round($totalProforma - $totalCommercial, 2);

            // Determine currency symbol
            $currency = '$';
            $deal = $contract->sale ?: $contract->purchase;
            if ($deal && $deal->currency && !empty($deal->currency->encode_html_symbol)) {
                $currency = $deal->currency->encode_html_symbol;
            } elseif (preg_match('/([$€£¥₹]|USD|EUR|GBP)/', $htmlCommercial, $cm)) {
                $currency = $cm[1];
            }

            $hasDiff = abs($diff) >= 0.01;
            $type = null;
            $amount = 0.0;
            $reason = '';

            if ($hasDiff) {
                if ($diff > 0) {
                    // Proforma > Commercial: Credit Note (customer paid more / invoiced higher than actual delivery)
                    $type = 'CREDIT';
                    $amount = $diff;
                    $reason = "Proforma total ({$currency} " . number_format($totalProforma, 2) . ") exceeds Commercial Invoice ({$currency} " . number_format($totalCommercial, 2) . ")";
                } else {
                    // Commercial > Proforma: Debit Note (commercial cost higher than initial proforma)
                    $type = 'DEBIT';
                    $amount = abs($diff);
                    $reason = "Commercial Invoice ({$currency} " . number_format($totalCommercial, 2) . ") exceeds Proforma total ({$currency} " . number_format($totalProforma, 2) . ")";
                }
            }

            // Check if a Credit / Debit note already exists
            $existingNote = null;
            $existingFile = null;
            if ($type) {
                $existingNote = \App\Models\DetachedNote::where('contract_id', $contract->id)
                    ->where('kind', $type)
                    ->first();
                $existingFile = $files->first(function ($f) use ($type) {
                    return str_starts_with($f->name, $type . '_NOTE_');
                });
            }

            return [
                'has_difference' => $hasDiff,
                'type' => $type,
                'amount' => $amount,
                'formatted_amount' => number_format($amount, 2),
                'currency' => $currency,
                'proforma_total' => $totalProforma,
                'commercial_total' => $totalCommercial,
                'diff' => $diff,
                'proforma_file_id' => $proforma->id,
                'commercial_file_id' => $commercial->id,
                'already_has_note' => ($existingNote !== null || $existingFile !== null),
                'existing_note_id' => $existingNote?->id,
                'existing_file_id' => $existingFile?->id,
                'reason' => $reason,
            ];
        } catch (\Throwable $e) {
            \Log::error("Error calculating invoice difference for contract {$contract->id}: " . $e->getMessage());
            return $defaultResult;
        }
    }
}
