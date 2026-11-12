<?php

namespace App\Http\Controllers\Api;

use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends BaseApiController
{
    public function index(Request $request)
    {
        $size = (int) $request->input('size', 20);
        $filter = $request->input('filter');
        $name = $request->input('name');

        $query = Contact::with(['products', 'addresses.country', 'addresses.representatives', 'language'])
            ->where('active', true);

        if ($filter) {
            $query->where(function($q) use ($filter) {
                $q->where('name', 'like', "%{$filter}%")
                  ->orWhere('codeMeta', 'like', "%{$filter}%")
                  ->orWhere('vat', 'like', "%{$filter}%");
            });
        }

        if ($name) {
            $query->where('name', 'like', "%{$name}%");
        }

        $paginator = $query->orderBy('name')->paginate($size);
        return response()->json($this->paginateResponse($paginator));
    }

    public function show($id)
    {
        $contact = Contact::with(['products', 'addresses.country', 'addresses.representatives', 'language'])->findOrFail($id);
        return response()->json($contact);
    }

    public function store(Request $request)
    {
        $contact = Contact::create($request->all());
        if ($request->has('typeIds')) {
            $contact->types()->sync($request->input('typeIds'));
        }
        if ($request->has('productIds')) {
            $contact->products()->sync($request->input('productIds'));
        }
        return response()->json($contact, 201);
    }

    public function update(Request $request, $id)
    {
        $contact = Contact::findOrFail($id);
        $contact->update($request->all());
        if ($request->has('typeIds')) {
            $contact->types()->sync($request->input('typeIds'));
        }
        if ($request->has('productIds')) {
            $contact->products()->sync($request->input('productIds'));
        }
        return response()->json($contact);
    }

    public function destroy($id)
    {
        $contact = Contact::findOrFail($id);
        $contact->active = false;
        $contact->save();
        return response()->json(['message' => 'Contact deleted']);
    }
}
