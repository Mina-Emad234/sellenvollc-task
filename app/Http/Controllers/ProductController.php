<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ResponseTrait;

    public function __construct(ProductService $productService) {
        $this->productService = $productService;
    }

    /**
     * index (all products)
     *
     * @return void
     */
    public function index()
    {
        return $this->productService->all();
    }

    /**
     * create new product
     *
     * @param  mixed $request
     * @return void
     */
    public function create(Request $request)
    {
        return $this->productService->create($request);
    }
}
