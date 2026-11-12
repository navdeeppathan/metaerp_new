<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\DetachedNote;
use App\Models\DetachedNoteDetail;
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
            'details.dealProduct',
            'details.creditDebitType'
        ];

        if ($selectedCode) {
            $selectedContract = Contract::where('order_code', $selectedCode)
                ->orWhere('id', $selectedCode)
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

        $companies = Company::where('active', true)->orderBy('name')->get();
        $contacts = Contact::where('active', true)->orderBy('name')->get();
        $currencies = Currency::all();

        return view('detached-notes.index', compact(
            'contracts',
            'detachedNotes',
            'selectedCode',
            'selectedContract',
            'companies',
            'contacts',
            'currencies'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kind' => 'required|string|in:CREDIT,DEBIT',
            'number' => 'nullable|string|max:64',
            'date' => 'nullable|date',
            'meta_company_id' => 'nullable|integer',
            'contact_id' => 'nullable|integer',
            'currency_id' => 'nullable|integer',
            'total' => 'nullable|numeric',
        ]);

        $contractId = null;
        if ($request->filled('contract_code')) {
            $c = Contract::where('order_code', $request->input('contract_code'))->first();
            $contractId = $c?->id;
        }

        $note = DetachedNote::create([
            'kind' => $validated['kind'],
            'contract_id' => $contractId,
            'complement' => $validated['number'] ?? ('DN-' . date('y') . '-' . rand(100, 999)),
        ]);

        if ($request->has('descriptions')) {
            $descriptions = $request->input('descriptions', []);
            $quantities = $request->input('quantities', []);
            $unitPrices = $request->input('unit_prices', []);

            foreach ($descriptions as $i => $desc) {
                if (!empty($desc)) {
                    $qty = (float)($quantities[$i] ?? 1);
                    $price = (float)($unitPrices[$i] ?? 0);
                    DetachedNoteDetail::create([
                        'detached_note_id' => $note->id,
                        'complement' => $desc,
                        'amount' => $qty * $price,
                    ]);
                }
            }
        }

        return redirect()->route('detached-notes.index', ['code' => $request->input('contract_code')])
            ->with('success', 'Detached note created successfully');
    }

    public function destroy($id)
    {
        $note = DetachedNote::findOrFail($id);
        $note->delete();

        return redirect()->route('detached-notes.index')->with('success', 'Detached note deleted successfully');
    }
}
