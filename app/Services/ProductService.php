<?php

namespace App\Services;

use App\Traits\ResponseTrait;

class ProductService {
    use ResponseTrait;

    private $token;
    private $key;
    private $secret;
    private $store;
    private $headers;

    public function __construct() {
        $this->token = env("SHOPIFY_ACCESS_TOKEN");
        $this->key = env("SHOPIFY_API_KEY");
        $this->secret = env("SHOPIFY_API_SECRET");
        $this->store = env("SHOPIFY_STORE_NAME");
        $this->headers = [
            'X-Shopify-access-token'=> $this->token,
            'Content-Type' => 'application/json',
            'Custom-Header' => 'custom-value',
        ];
    }


    /**
     * get all filtered products
     *
     * @return void
     */
    public function all()
    {
        try {
            $client = new \GuzzleHttp\Client();

            $response = $client->request('GET', "https://$this->key:$this->secret@$this->store.myshopify.com/admin/products.json",[
                'headers' => $this->headers,
            ]);
            return $this->filterProduct(json_decode($response->getBody(), true));
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return $this->handleErrorMsg($e->getMessage(),$e->getCode());
        }catch (\GuzzleHttp\Exception\ServerException  $e) {
            return $this->handleErrorMsg($e->getMessage(),$e->getCode());
        }
    }

    /**
     * Using API to create a new product
     *
     * @param  mixed $data
     * @return void
     */
    public function create($data)
    {
        try {
            $client = new \GuzzleHttp\Client();

            $response = $client->request('POST', "https://$this->key:$this->secret@$this->store.myshopify.com/admin/products.json",[
                'headers' => $this->headers,
                'json' => $data->all(),
            ]);
            return $this->returnData('Data', $response->getBody());

            return $this->returnSuccessMessage('Product Created Successfully');
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return $this->handleErrorMsg($e->getMessage(),$e->getCode());
        }catch (\GuzzleHttp\Exception\ServerException  $e) {
            return $this->handleErrorMsg($e->getMessage(),$e->getCode());
        }
    }


    /**
     * filterProduct (filter outer collection)
     *
     * @param  mixed $products
     * @return void
     */
    public function filterProduct($products)
    {
        return collect($products['products'])->map(function ($product) {
            return collect($product)->map(function ($value) {
                if (is_array($value)) {
                    return $this->filterArrayValue($value);
                } else {
                    return $value;
                }
            })->filter()->all();
        })->all();
    }

    /**
     * filterArrayValue (filter inner arrays)
     *
     * @param  mixed $value
     * @return void
     */
    protected function filterArrayValue($value)
    {
        return collect($value)->map(function ($val) {
            if (array_key_exists('inventory_quantity', collect($val)->toArray())) {
                $val['inventory_quantity'] = 50;
            }
            return collect($val)->filter(function ($v) {
                return $this->isValidValue($v);
            })->all();
        })->filter()->all();
    }

    /**
     * isValidValue (return valid values)
     *
     * @param  mixed $v
     * @return void
     */
    protected function isValidValue($v)
    {
        return !empty($v) && !is_null($v);
    }

}
