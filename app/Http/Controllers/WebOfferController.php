<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Product;
use App\Models\Currency;
use Illuminate\Http\Request;

class WebOfferController extends Controller
{
    public function index(Request $request)
    {
        $query = Offer::query();

        $search = $request->input('q') ?? $request->input('search');
        if (!empty($search)) {
            $query->where(function($sub) use ($search) {
                $sub->where('subject', 'like', "%{$search}%")
                    ->orWhere('order_code', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $offers = $query->orderBy('id', 'desc')->paginate($perPage)->withQueryString();
        return view('offers.index', compact('offers'));
    }

    public function create()
    {
        $offer = new Offer();
        return view('offers.create', compact('offer'));
    }

    public function show($id)
    {
        $offer = Offer::findOrFail($id);
        return view('offers.create', compact('offer'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'offer_date' => 'nullable|string',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'internal_notes' => 'nullable|string',
            'delivery_plan' => 'nullable|string',
            'quotational_period' => 'nullable|string',
            'weights' => 'nullable|string',
            'payment_description' => 'nullable|string',
            'terms' => 'nullable|string',
            'extra_information' => 'nullable|string',
        ]);

        $maxId = Offer::max('id') ?? 21;
        $nextId = $maxId + 1;
        $code = 'OFFER-' . str_pad((string)$nextId, 5, '0', STR_PAD_LEFT);

        $offer = Offer::create([
            'id' => $nextId,
            'status' => 'OPEN',
            'type' => $validated['type'],
            'subject' => $validated['subject'],
            'code' => $code,
            'orderCode' => $code,
            'offer_date' => $request->filled('offer_date') ? date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $request->input('offer_date')))) : now(),
            'description' => $validated['description'] ?? null,
            'internal_notes' => $validated['internal_notes'] ?? null,
            'delivery_plan' => $validated['delivery_plan'] ?? null,
            'quotational_period' => $validated['quotational_period'] ?? null,
            'weights' => $validated['weights'] ?? null,
            'payment_description' => $validated['payment_description'] ?? null,
            'terms' => $validated['terms'] ?? null,
            'extra_information' => $validated['extra_information'] ?? null,
        ]);

        return redirect()->route('offers.show', $offer->id)->with('success', 'Offer created successfully!');
    }

    public function update(Request $request, $id)
    {
        $offer = Offer::findOrFail($id);

        $data = $request->only([
            'type', 'subject', 'description', 'internal_notes',
            'delivery_plan', 'quotational_period', 'weights',
            'payment_description', 'terms', 'extra_information'
        ]);

        if ($request->filled('offer_date')) {
            $data['offer_date'] = date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $request->input('offer_date'))));
        }

        $offer->update($data);

        return redirect()->route('offers.show', $offer->id)->with('success', 'Offer details updated successfully!');
    }

    public function destroy($id)
    {
        $offer = Offer::findOrFail($id);
        $offer->delete();

        return redirect()->route('offers.index')->with('success', 'Offer deleted successfully!');
    }

    public function convertToContract($id)
    {
        $offer = Offer::findOrFail($id);
        $offer->status = 'CONVERTED';
        $offer->save();

        return redirect()->route('contracts.board')->with('success', "Offer {$offer->code} converted to contract!");
    }
}
