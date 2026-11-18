<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\DetachedNote;
use App\Models\DetachedNoteDetail;
use App\Models\CreditDebitType;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Currency;
use Illuminate\Http\Request;

class WebDetachedNoteController extends Controller
{
    public function index(Request $request)
    {
        $contracts = Contract::whereNotNull('order_code')
            ->orderByDesc('id')
            ->get(['id', 'order_code', 'type']);

        $selectedCode = $request->input('code');
        $selectedContract = null;
        $detachedNotes = collect();

        $withRelations = [
            'contract.purchase.metaCompany',
            'contract.purchase.contact',
            'contract.purchase.currency',
            'contract.sale.metaCompany',
            'contract.sale.contact',
            'contract.sale.currency',
            'details.dealProduct.product',
            'details.creditDebitType',
            'generatedFile'
        ];

        if ($selectedCode) {
            $selectedContract = Contract::where('order_code', $selectedCode)
                ->orWhere('id', $selectedCode)
                ->with([
                    'purchase.products.product',
                    'sale.products.product',
                    'purchase.metaCompany',
                    'purchase.contact',
                    'purchase.currency',
                    'sale.metaCompany',
                    'sale.contact',
                    'sale.currency'
                ])
                ->first();

            if ($selectedContract) {
                $detachedNotes = DetachedNote::with($withRelations)
                    ->where('contract_id', $selectedContract->id)
                    ->orderByDesc('id')
                    ->get();
            }
        } else {
            $detachedNotes = DetachedNote::with($withRelations)
                ->orderByDesc('id')
                ->get();
        }

        try {
            $creditDebitTypes = CreditDebitType::where('active', true)->orderBy('id')->get();
            if ($creditDebitTypes->isEmpty()) {
                $creditDebitTypes = $this->getDefaultCreditDebitTypes();
            }
        } catch (\Exception $e) {
            $creditDebitTypes = $this->getDefaultCreditDebitTypes();
        }

        try {
            $companies = Company::where('active', true)->orderBy('name')->get();
        } catch (\Exception $e) {
            $companies = collect();
        }

        try {
            $contacts = Contact::where('active', true)->orderBy('name')->get();
        } catch (\Exception $e) {
            $contacts = collect();
        }

        try {
            $currencies = Currency::all();
        } catch (\Exception $e) {
            $currencies = collect();
        }

        return view('detached-notes.index', compact(
            'contracts',
            'detachedNotes',
            'selectedCode',
            'selectedContract',
            'creditDebitTypes',
            'companies',
            'contacts',
            'currencies'
        ));
    }

    public function getProducts($contractId)
    {
        $contract = Contract::with([
            'purchase.products.product',
            'sale.products.product'
        ])->findOrFail($contractId);

        $products = collect();

        if ($contract->purchase && $contract->purchase->products) {
            foreach ($contract->purchase->products as $p) {
                $products->push([
                    'id' => $p->id,
                    'deal_type' => 'Purchase',
                    'code' => $p->product?->code ?? ('PROD-' . $p->id),
                    'name' => $p->product?->name ?? ($p->description ?? 'Deal Product #' . $p->id),
                    'quantity' => $p->quantity,
                    'rate' => $p->rate,
                ]);
            }
        }

        if ($contract->sale && $contract->sale->products) {
            foreach ($contract->sale->products as $p) {
                $products->push([
                    'id' => $p->id,
                    'deal_type' => 'Sale',
                    'code' => $p->product?->code ?? ('PROD-' . $p->id),
                    'name' => $p->product?->name ?? ($p->description ?? 'Deal Product #' . $p->id),
                    'quantity' => $p->quantity,
                    'rate' => $p->rate,
                ]);
            }
        }

        return response()->json($products);
    }

    public function show($id)
    {
        $note = DetachedNote::with([
            'contract.purchase.metaCompany',
            'contract.purchase.contact',
            'contract.purchase.currency',
            'contract.sale.metaCompany',
            'contract.sale.contact',
            'contract.sale.currency',
            'details.dealProduct.product',
            'details.creditDebitType',
            'generatedFile'
        ])->findOrFail($id);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json($note);
        }

        return view('detached-notes.show', compact('note'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kind' => 'required|string|in:CREDIT,DEBIT',
            'contract_id' => 'required|integer',
            'complement' => 'nullable|string|max:500',
            'details' => 'nullable|array',
            'details.*.credit_debit_type_id' => 'nullable|integer',
            'details.*.deal_product_id' => 'nullable|integer',
            'details.*.container' => 'nullable|string|max:255',
            'details.*.amount' => 'nullable|numeric',
            'details.*.complement' => 'nullable|string|max:500',
        ]);

        $contract = Contract::findOrFail($validated['contract_id']);

        $note = DetachedNote::create([
            'kind' => $validated['kind'],
            'contract_id' => $contract->id,
            'complement' => $validated['complement'] ?? '',
        ]);

        if (!empty($validated['details'])) {
            foreach ($validated['details'] as $item) {
                if (isset($item['amount']) && $item['amount'] !== '') {
                    DetachedNoteDetail::create([
                        'detached_note_id' => $note->id,
                        'credit_debit_type_id' => !empty($item['credit_debit_type_id']) ? $item['credit_debit_type_id'] : null,
                        'deal_product_id' => !empty($item['deal_product_id']) ? $item['deal_product_id'] : null,
                        'container' => $item['container'] ?? null,
                        'amount' => (float)$item['amount'],
                        'complement' => $item['complement'] ?? null,
                    ]);
                }
            }
        }

        return redirect()->route('detached-notes.index', ['code' => $contract->orderCode])
            ->with('success', 'Detached note data have been saved');
    }

    public function destroy($id)
    {
        $note = DetachedNote::findOrFail($id);
        $contractCode = $note->contract?->orderCode;
        
        $note->details()->delete();
        $note->delete();

        $redirectParam = $contractCode ? ['code' => $contractCode] : [];

        return redirect()->route('detached-notes.index', $redirectParam)
            ->with('success', 'Detached note data have been removed');
    }

    public function downloadDocument($id)
    {
        $note = DetachedNote::with('generatedFile')->findOrFail($id);
        if (!$note->generatedFile || empty($note->generatedFile->content)) {
            return back()->with('error', 'No generated document available for this note.');
        }

        $raw = $note->generatedFile->content;
        $decoded = base64_decode($raw, true);
        $content = ($decoded !== false) ? $decoded : $raw;
        $filename = $note->generatedFile->name ?? ($note->kind . '_NOTE_' . $note->id . '.pdf');

        return response($content)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }

    private function getDefaultCreditDebitTypes()
    {
        return collect([
            (object)['id' => 1, 'description' => 'Difference - Tentative & Final LME pricing'],
            (object)['id' => 2, 'description' => 'Detention/Demurrage Charge'],
            (object)['id' => 3, 'description' => 'Customs Fine/Penalty'],
            (object)['id' => 4, 'description' => 'Additional Cost'],
            (object)['id' => 5, 'description' => 'Contract Canceled'],
            (object)['id' => 6, 'description' => 'Quality Difference'],
            (object)['id' => 7, 'description' => 'War Risk Surcharge (WRS) for all cargo due the WAR in the Middle East'],
            (object)['id' => 8, 'description' => 'Emergency Contingency Surcharges (ECS) for all cargo due the WAR in the Middle East'],
            (object)['id' => 9, 'description' => 'Transport Charges'],
            (object)['id' => 10, 'description' => 'New price for additional material.'],
            (object)['id' => 11, 'description' => 'Discrepancy in Quantity Identified During Receipt of Goods'],
            (object)['id' => 12, 'description' => 'Difference of incorrect Premium used on the CI'],
            (object)['id' => 13, 'description' => 'Bank charges due to share wrong BIC'],
            (object)['id' => 14, 'description' => 'Delay'],
            (object)['id' => 15, 'description' => 'Container Damage'],
            (object)['id' => 16, 'description' => 'Free Time Not applied from Shipping Line'],
        ]);
    }
}
