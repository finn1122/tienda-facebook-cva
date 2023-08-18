<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Group;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\DB;


class ProductController extends Controller
{
    public function create(Request $request){

        $validated = $request->validate([
            'name'          => 'required|max:255',
            'description'   => 'nullable|text',
            'warranty'      => 'nullable|max:255',
            'inventory'     => 'required|integer',
            'cva_price'     => 'required|float',
            'cva_currency'  => 'required|max:255',
            'price'         => 'nullable|float',
            'sale_price'    => 'nullable|float',
            'currency'      => 'required|max:255',
            'cva_key'       => 'required|max:255',
            'sku'           => 'required|max:255',
            'image_link'    => 'nullable|text',
            'brand_id'      => 'required|integer',
            'group_id'      => 'required|integer',
        ]);
    }

    public function getAllProducts()
    {
        Log::info('getAllProducts');
        //ALL PRODUCTS https://www.grupocva.com/catalogo_clientes_xml/lista_precios.xml?cliente=64302&marca=%&grupo=%&clave=%&codigo=%
        //ALL PHP PRODUCT https://www.grupocva.com/catalogo_clientes_xml/lista_precios.xml?cliente=64302&marca=HP&grupo=%&clave=%&codigo=%
        $products = 0;
        $flash_banner_color = 'danger';

        try{
            $xml_response = Http::get('https://www.grupocva.com/catalogo_clientes_xml/lista_precios.xml?cliente=64302&marca=HP&grupo=%&clave=%&codigo=%');

            $response = simplexml_load_string($xml_response);
            $json_response = json_decode(json_encode($response));

            DB::beginTransaction();

            foreach ($json_response->item as $array_product){
                if(isset($array_product->marca)){
                    //[SAVE ALL BRANDS]
                    $brand = Brand::updateOrCreate(
                        ['name' => isset($array_product->marca)],
                        ['active' => true]
                    );
                }
                if(isset($array_product->marca)) {
                    //[SAVE ALL GROUPS]
                    $group = Group::updateOrCreate(
                        ['name' => isset($array_product->grupo)],
                        ['active' => true]
                    );
                }

                if(isset($brand->id) && isset($group->id) && $array_product->disponible > 0){
                    //[SAVE ALL PRODUCTS]
                    $product = new Product();
                    $product->sku          = isset($array_product->codigo_fabricante);
                    $product->name         = $array_product->descripcion;
                    $product->warranty     = $array_product->garantia;
                    $product->inventory    = $array_product->disponible;
                    $product->cva_price    = $array_product->precio;
                    $product->cva_currency = $array_product->moneda;
                    $product->cva_key      = $array_product->clave;
                    $product->brand_id     = $brand->id;
                    $product->group_id     = $group->id;
                    $product->active       = true;
                    $product->save();
                }
            }
            DB::commit();
            $products = Product::all()->count();
            $flash_banner_color = 'success';
        }catch (Exception $e){
            DB::rollBack();
            Log::debug($e->getMessage());
        }

        $products = $products > 0 ? $products : 0;

        session()->flash('flash.banner', 'Se han agregado: '.$products.' productos.');
        session()->flash('flash.bannerStyle', $flash_banner_color);

        return redirect()->back();
    }

    public function updateAllProducts(): JsonResponse{
        Log::info('updateAllProducts');
        //https://www.grupocva.com/catalogo_clientes_xml/lista_precios.xml?cliente=64302&marca=%&grupo=%&clave=%&codigo=%
        try{
            $xml_response = Http::get('https://www.grupocva.com/catalogo_clientes_xml/lista_precios.xml?cliente=64302&marca=HP&grupo=%&clave=%&codigo=%');

            $response = simplexml_load_string($xml_response);
            $json_response = json_encode($response);
            $json_response = json_decode($json_response,TRUE);

            foreach ($json_response['item'] as $array_product){
                //[SAVE ALL BRANDS]
                Brand::updateOrCreate(
                    ['name' => $array_product['marca']],
                    ['active' => true]
                );
                //[SAVE ALL GROUPS]
                Group::updateOrCreate(
                    ['name' => $array_product['grupo']],
                    ['active' => true]
                );

                //FIND IDs
                $brand = Brand::where('name', $array_product['marca'])->first();
                $group = Group::where('name', $array_product['grupo'])->first();

                //[SAVE ALL PRODUCTS]
                Product::updateOrCreate(
                    ['sku' => $array_product['codigo_fabricante']],
                    [
                         //'name'         => $array_product['descripcion']
                        'warranty'      => $array_product['garantia']
                        ,'inventory'    => $array_product['disponible']
                        ,'cva_price'    => $array_product['precio']
                        ,'cva_currency' => $array_product['moneda']
                        ,'cva_key'      => $array_product['clave']
                        ,'brand_id'     => $brand->id
                        ,'group_id'     => $group->id
                        ,'active'       => true
                    ]);
            }


        }catch (\Exception $e){
            Log::debug($e->getMessage());
            response()->json(['error' => $e->getMessage()], $xml_response->status());

        }
        return response()->json(['success' => 'success'], 200);

    }
}
