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
        $contracts = Contract::whereNotNull('orderCode')
            ->orderByDesc('id')
            ->get(['id', 'orderCode', 'type']);

        $selectedCode = $request->input('code');
        $selectedContract = null;
        $detachedNotes = collect();

        if ($selectedCode) {
            $selectedContract = Contract::where('orderCode', $selectedCode)
                ->orWhere('id', $selectedCode)
                ->first();

            if ($selectedContract) {
                // If notes are associated or exist
                $detachedNotes = DetachedNote::with(['metaCompany', 'contact', 'currency', 'details'])
                    ->orderByDesc('id')
                    ->get();
            }
        } else {
            $detachedNotes = DetachedNote::with(['metaCompany', 'contact', 'currency', 'details'])
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

        $note = DetachedNote::create([
            'kind' => $validated['kind'],
            'number' => $validated['number'] ?? ('DN-' . date('y') . '-' . rand(100, 999)),
            'date' => $validated['date'] ?? now(),
            'meta_company_id' => $validated['meta_company_id'] ?? null,
            'contact_id' => $validated['contact_id'] ?? null,
            'currency_id' => $validated['currency_id'] ?? 1,
            'total' => $validated['total'] ?? 0,
            'version' => 0,
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
                        'description' => $desc,
                        'quantity' => $qty,
                        'unitPrice' => $price,
                        'total' => $qty * $price,
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
