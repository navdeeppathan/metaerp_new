<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Template;
use App\Models\GeneratedFile;
use Illuminate\Support\Facades\Auth;

class DocumentTemplateService
{
    /**
     * Solve and render a template for a contract.
     */
    public function render(Template $template, Contract $contract, ?int $userId = null): GeneratedFile
    {
        $deal = $contract->purchase ?: $contract->sale;
        $metaCompany = $deal?->metaCompany;
        $contact = $deal?->contact;
        $address = $deal?->address;
        $currency = $deal?->currency?->description ?? 'USD';

        $totalAmount = number_format($deal?->total ?? 0, 2) . ' ' . $currency;

        // Build product table HTML
        $productTable = '<table style="width:100%; border-collapse:collapse; margin:15px 0;">'
            . '<thead><tr style="background:#f3f4f6; text-align:left;">'
            . '<th style="border:1px solid #d1d5db; padding:8px;">Product</th>'
            . '<th style="border:1px solid #d1d5db; padding:8px;">Quantity</th>'
            . '<th style="border:1px solid #d1d5db; padding:8px;">Unit Price</th>'
            . '<th style="border:1px solid #d1d5db; padding:8px;">Total</th>'
            . '</tr></thead><tbody>';

        if ($deal && $deal->products) {
            foreach ($deal->products as $p) {
                $pName = htmlspecialchars($p->product?->name ?? 'Commodity');
                $qty = number_format($p->quantity, 2);
                $price = number_format($p->unitPrice, 2) . ' ' . $currency;
                $tot = number_format($p->total, 2) . ' ' . $currency;
                $productTable .= "<tr><td style=\"border:1px solid #d1d5db; padding:8px;\">{$pName}</td>"
                    . "<td style=\"border:1px solid #d1d5db; padding:8px;\">{$qty}</td>"
                    . "<td style=\"border:1px solid #d1d5db; padding:8px;\">{$price}</td>"
                    . "<td style=\"border:1px solid #d1d5db; padding:8px;\">{$tot}</td></tr>";
            }
        }
        $productTable .= '</tbody></table>';

        // Packing list tokens
        $pl = $contract->packingLists->first();
        $packingTable = '<table style="width:100%; border-collapse:collapse; margin:15px 0;">'
            . '<thead><tr style="background:#f3f4f6; text-align:left;">'
            . '<th style="border:1px solid #d1d5db; padding:8px;">Container</th>'
            . '<th style="border:1px solid #d1d5db; padding:8px;">Seal</th>'
            . '<th style="border:1px solid #d1d5db; padding:8px;">Packages</th>'
            . '<th style="border:1px solid #d1d5db; padding:8px;">Net Wt (kg)</th>'
            . '<th style="border:1px solid #d1d5db; padding:8px;">Gross Wt (kg)</th>'
            . '</tr></thead><tbody>';

        if ($pl && $pl->packs) {
            foreach ($pl->packs as $pack) {
                $cnt = htmlspecialchars($pack->containerNumber ?? 'N/A');
                $seal = htmlspecialchars($pack->sealNumber ?? 'N/A');
                $q = $pack->quantity;
                $nw = number_format($pack->netWeight, 2);
                $gw = number_format($pack->grossWeight, 2);
                $packingTable .= "<tr><td style=\"border:1px solid #d1d5db; padding:8px;\">{$cnt}</td>"
                    . "<td style=\"border:1px solid #d1d5db; padding:8px;\">{$seal}</td>"
                    . "<td style=\"border:1px solid #d1d5db; padding:8px;\">{$q}</td>"
                    . "<td style=\"border:1px solid #d1d5db; padding:8px;\">{$nw}</td>"
                    . "<td style=\"border:1px solid #d1d5db; padding:8px;\">{$gw}</td></tr>";
            }
        }
        $packingTable .= '</tbody></table>';

        $replacements = [
            '{meta_company_name}' => $metaCompany?->name ?? 'Metaenterprices Ltd',
            '{meta_company_number}' => $metaCompany?->companyNumber ?? '',
            '{meta_company_vat}' => $metaCompany?->vatNumber ?? '',
            '{meta_company_address_line1}' => $metaCompany?->address?->addressLine1 ?? '',
            '{meta_company_address_city}' => $metaCompany?->address?->city ?? '',
            '{meta_company_tel}' => $metaCompany?->phoneNumber ?? '',
            '{meta_company_email}' => $metaCompany?->email ?? '',
            '{contract_buyer_name}' => $contact?->name ?? '',
            '{contract_buyer_address_line1}' => $address?->addressLine1 ?? '',
            '{contract_buyer_address_city}' => $address?->city ?? '',
            '{contract_buyer_country}' => $address?->country?->name ?? '',
            '{contract_order_code}' => $contract->orderCode ?? '',
            '{contract_date}' => $contract->date ? $contract->date->format('d/m/Y') : date('d/m/Y'),
            '{contract_delivery_terms}' => $contract->deliveryTerms ?? '',
            '{contract_mode_of_payment}' => $contract->modeOfPayment ?? '',
            '{contract_total_amount}' => $totalAmount,
            '{contract_product_table}' => $productTable,
            '{packing_list_vessel}' => $pl?->vesselName ?? 'TBA',
            '{packing_list_bl}' => $pl?->blNumber ?? 'TBA',
            '{packing_list_table}' => $packingTable,
        ];

        // Validate required keys
        $requiredKeys = $template->keys_list;
        $missingKeys = [];
        foreach ($requiredKeys as $rk) {
            $val = $replacements[$rk] ?? null;
            if ($val === null || $val === '') {
                $missingKeys[] = $rk;
            }
        }

        if (!empty($missingKeys)) {
            throw new \Exception("Required keys not provided: " . implode(', ', $missingKeys), 409);
        }

        $html = str_replace(array_keys($replacements), array_values($replacements), $template->html);

        $fileName = sprintf(
            '%s_%s_%s.html',
            strtoupper($template->type),
            preg_replace('/[^A-Za-z0-9_-]/', '', $contract->orderCode ?? 'DOC'),
            date('YmdHis')
        );

        return GeneratedFile::create([
            'contract_id' => $contract->id,
            'template_id' => $template->id,
            'fileName' => $fileName,
            'html' => $html,
            'fileType' => 'HTML',
            'user_id' => $userId ?: (Auth::id() ?: 1),
        ]);
    }
}
