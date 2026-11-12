<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\ContractOrderCodeSequence;
use App\Models\Company;
use App\Models\Deal;
use Illuminate\Support\Facades\DB;

class OrderCodeService
{
    /**
     * Generate sequential order code formatted as:
     * {COMPANY_CODE}{PRODUCT_CATEGORY}{YY}-{SEQ}
     */
    public function orderCodeGenerator(Contract $contract, bool $keepYear = false): string
    {
        $currentYear = (int) date('Y');
        $twoDigitYear = date('y');

        $deal = $this->selectDeal($contract);
        if (!$deal) {
            throw new \IllegalStateException("Either Purchase or Sale deal is missing");
        }

        if (!$deal->meta_company_id && !$deal->metaCompany) {
            throw new \IllegalStateException("Meta Company information is missing");
        }

        $company = $deal->metaCompany ?? Company::find($deal->meta_company_id);
        if (!$company) {
            throw new \NoSuchElementException("Meta company was not found");
        }

        $productCategory = $this->getProductCategoryLabel($deal);

        // Fetch or create sequence row under transaction lock
        $seq = DB::transaction(function () use ($currentYear) {
            $record = ContractOrderCodeSequence::firstOrCreate(
                ['year' => $currentYear],
                ['last_id' => 0]
            );
            $record->last_id += 1;
            $record->save();
            return $record->last_id;
        });

        $orderCode = $contract->order_code ?? $contract->orderCode ?? null;
        if ($keepYear && !empty($orderCode) && str_contains($orderCode, '-')) {
            $dashIndex = strpos($orderCode, '-');
            if ($dashIndex >= 2) {
                $twoDigitYear = substr($orderCode, $dashIndex - 2, 2);
            }
        }

        return sprintf(
            '%s%s%s-%d',
            strtoupper(trim($company->code)),
            trim($productCategory),
            $twoDigitYear,
            $seq
        );
    }

    private function selectDeal(Contract $contract): ?Deal
    {
        return $contract->purchase ?: $contract->sale;
    }

    private function getProductCategoryLabel(Deal $deal): string
    {
        if ($deal->products && $deal->products->isNotEmpty()) {
            $dealProduct = $deal->products->first();
            if ($dealProduct->product && !empty($dealProduct->product->productCategory)) {
                return $dealProduct->product->productCategory;
            }
        }
        return 'COMMODITY';
    }
}
