<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\DealProduct;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class IllegalStateException extends RuntimeException {}

class ContractAssociationService
{
    /**
     * Check if a purchase contract can be closed.
     * All purchase product quantities must be sold/associated.
     */
    public function verifyPurchaseContractSaleAssociations(int $purchaseContractId): void
    {
        $contract = Contract::with(['purchase.products.product'])->findOrFail($purchaseContractId);

        if (!$contract->purchase || $contract->purchase->products->isEmpty()) {
            throw new \IllegalStateException("Purchase deal does not exist or is empty");
        }

        // Compare purchase product quantities against sales deal products linked to this purchase contract
        $purchaseProducts = $contract->purchase->products;
        $differences = [];

        foreach ($purchaseProducts as $pp) {
            $soldQty = DealProduct::where('associatedContractId', $purchaseContractId)
                ->where('product_id', $pp->product_id)
                ->sum('quantity');

            $diff = $pp->quantity - $soldQty;
            if ($diff > 0.0001) {
                $differences[$pp->product?->name ?? 'Product'] = $diff;
            }
        }

        if (!empty($differences)) {
            $msg = "Contract cannot be closed. Products not yet sold:\n";
            foreach ($differences as $prodName => $qtyRemaining) {
                $msg .= "{$prodName}: {$qtyRemaining}\n";
            }
            throw new \IllegalStateException($msg);
        }
    }

    /**
     * Determine association status for a contract.
     */
    public function getAssociationStatus(Contract $contract): string
    {
        if ($contract->type !== 'SELL') {
            return 'NOT_APPLY';
        }

        if (!$contract->sale || $contract->sale->products->isEmpty()) {
            return 'PENDING';
        }

        $total = $contract->sale->products->count();
        $associated = $contract->sale->products->whereNotNull('associatedContractId')->count();

        if ($associated === $total) {
            return 'COMPLETE';
        }

        return $associated === 0 ? 'PENDING' : 'PARTIAL';
    }

    /**
     * Associate a sales deal product with a purchase contract.
     */
    public function associate(int $dealProductId, int $purchaseContractId): DealProduct
    {
        $dp = DealProduct::findOrFail($dealProductId);
        $purchaseContract = Contract::findOrFail($purchaseContractId);

        $dp->associatedContractId = $purchaseContractId;
        $dp->status = 'ASSOCIATED';
        $dp->save();

        return $dp;
    }
}
