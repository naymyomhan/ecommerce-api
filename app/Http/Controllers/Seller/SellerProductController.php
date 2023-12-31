<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\CreateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SellerProductController extends Controller
{
    use ResponseTrait;

    public function createProduct(CreateProductRequest $request)
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validated();
            $validatedData['seller_id'] = Auth::id();
            $new_product = Product::create($validatedData);
            DB::commit();
            return $this->success("Create new product successful", $new_product);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('SellerProductController : createProduct() :' . $th->getMessage());
            return $this->fail("Something went wrong", 500);
        }
    }


    public function getAllProducts(Request $request)
    {
        $query = Product::query();

        //Filters
        $query->when($request->filled('category_id'), function ($query) use ($request) {
            $query->where('category_id', $request->category_id);
        });

        $query->when($request->filled('sub_category_id'), function ($query) use ($request) {
            $query->where('sub_category_id', $request->sub_category_id);
        });
        // Add more filters price range, brand, search keyword

        $query->when($request->filled('search'), function ($query) use ($request) {
            $query->where(function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%')
                    ->orWhereHas('brand', function ($query) use ($request) {
                        $query->where('name', 'like', '%' . $request->search . '%');
                    })
                    ->orWhereHas('category', function ($query) use ($request) {
                        $query->where('name', 'like', '%' . $request->search . '%');
                    })
                    ->orWhereHas('subCategory', function ($query) use ($request) {
                        $query->where('name', 'like', '%' . $request->search . '%');
                    });
            });
        });

        $products = $query->with(['category', 'subCategory', 'seller', 'brand'])->paginate(5);
        // return $this->success("Get Products Successful", ProductResource::collection($products));
        // return $this->success("Get Products Successful", $products);
        return $this->success("Book List", [
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'per_page' => $products->perPage(),
            'data' => ProductResource::collection($products->items()),
        ]);
    }
}
