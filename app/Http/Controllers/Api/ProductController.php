<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends BaseApiController
{
    public function index(Request $request)
    {
        $size = (int) $request->input('size', 20);
        $paginator = Product::with('contact')->where('active', true)->orderBy('name')->paginate($size);

        $transformed = $paginator->getCollection()->map(function ($p) {
            return $this->formatProductDto($p);
        });
        $paginator->setCollection($transformed);

        return response()->json($this->paginateResponse($paginator));
    }

    public function show($id)
    {
        $product = Product::with('contact')->findOrFail($id);
        return response()->json($this->formatProductDto($product));
    }

    public function store(Request $request)
    {
        $product = new Product();
        $this->applyProductFields($product, $request->all());
        $product->active = true;
        $product->save();

        return response()->json($this->formatProductDto($product), 201);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $this->applyProductFields($product, $request->all());
        $product->save();

        return response()->json($this->formatProductDto($product));
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->active = false;
        $product->save();
        return response()->json(['message' => 'Product deleted']);
    }

    private function applyProductFields(Product $p, array $data): void
    {
        if (isset($data['name'])) $p->name = $data['name'];
        if (isset($data['symbol'])) $p->symbol = $data['symbol'];
        if (isset($data['code'])) $p->code = $data['code'];
        if (isset($data['active'])) $p->active = (bool) $data['active'];

        if (isset($data['UOM']) || isset($data['uom'])) {
            $p->uom = $data['UOM'] ?? $data['uom'];
        }
        if (isset($data['itemSpecification']) || isset($data['item_specification'])) {
            $p->item_specification = $data['itemSpecification'] ?? $data['item_specification'];
        }
        if (isset($data['baseCodeAnnexIX']) || isset($data['base_code_annexix'])) {
            $p->base_code_annexix = $data['baseCodeAnnexIX'] ?? $data['base_code_annexix'];
        }
        if (isset($data['euListOfWastes']) || isset($data['eu_list_of_wastes'])) {
            $p->eu_list_of_wastes = $data['euListOfWastes'] ?? $data['eu_list_of_wastes'];
        }
        if (isset($data['hsCode']) || isset($data['hs_code'])) {
            $p->hs_code = $data['hsCode'] ?? $data['hs_code'];
        }
        if (isset($data['productCategory']) || isset($data['product_category'])) {
            $p->product_category = $data['productCategory'] ?? $data['product_category'];
        }
        if (isset($data['contactId']) || isset($data['contact_id'])) {
            $p->contact_id = $data['contactId'] ?? $data['contact_id'];
        }
    }

    private function formatProductDto(Product $p): array
    {
        return [
            'id' => $p->id,
            'name' => $p->name,
            'symbol' => $p->symbol,
            'itemSpecification' => $p->item_specification,
            'code' => $p->code,
            'baseCodeAnnexIX' => $p->base_code_annexix,
            'UOM' => $p->uom,
            'uom' => $p->uom,
            'euListOfWastes' => $p->eu_list_of_wastes,
            'active' => (bool) $p->active,
            'hsCode' => $p->hs_code,
            'productCategory' => $p->product_category,
            'contact' => $p->contact,
        ];
    }
}
