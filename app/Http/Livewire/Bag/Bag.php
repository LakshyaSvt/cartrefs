<?php

namespace App\Http\Livewire\Bag;

use App\Productsku;
use App\Models\Product;
use Livewire\Component;
use Darryldecode\Cart\Cart;
use Seshac\Shiprocket\Shiprocket;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

class Bag extends Component
{
    public $deliveryavailability = false;
    public $pickuppincode;
    public $deliverypincode;


    public function mount()
    {
        $this->pickuppincode = setting('seller-name.pincode');
        $this->deliverypincode = Session::get('deliverypincode');

        
    }


    public function render()
    {
        // \Cart::clear();

        $carts = \Cart::getContent();

        if(count($carts) == 0)
        {
            Session::remove('deliverypincode');
            Session::remove('deliveryavailable');
            Session::remove('deliverynotavailable');
        }

        $subtotal = \Cart::getSubTotal();

        $total = \Cart::getTotal();


        if(!empty(Session::get('deliverypincode')))
        {
            // $this->deliveryavailability = true;
            // Session::flash('deliveryavailable', 'Delivery available in your area');
        }else{
            Session::remove('deliveryavailable');
            Session::remove('deliverynotavailable');
        }
        
        return view('livewire.bag.bag')->with([
            'carts' => $carts,
            'subtotal' => $subtotal,
            'total' => $total
        ]);
    }

    public function plusqty($cartid)
    {
        $cart = \Cart::get($cartid);

        /**
         * If stock management is enabled then check if we have available stock
         */
        if(Config::get('icrm.stock_management.feature') == 1)
        {
            if(Config::get('icrm.product_sku.color') == 1)
            {
                $availablestock = Productsku::where('product_id', $cart->attributes->product_id)
                    ->where('size', $cart->attributes->size)
                    ->where('color', $cart->attributes->color)
                    ->first()->available_stock;
            }else{
                $availablestock = Productsku::where('product_id', $cart->attributes->product_id)
                    ->where('size', $cart->attributes->size)
                    ->first()->available_stock;
            }

            
            if($cart->quantity == $availablestock)
            {
                Session::flash($cartid.'qtynotavailable', 'Only '.$availablestock.' item left');
                return;
            }
            
        }


        \Cart::update($cartid, array(
            'quantity' => 1, // so if the current product has a quantity of 4, another 2 will be added so this will result to 6
            'attributes' => array(
                'size' => $cart->attributes->size,
                'product_id' => $cart->attributes->product_id,
                'customized_image' => $cart->attributes->customized_image,
                'original_file' => $cart->attributes->original_file,
                'color' => $cart->attributes->color,
                'g_plus' => $cart->attributes->g_plus,
                'cost_per_g' => $cart->attributes->cost_per_g,
                'g_plus_charges' => $cart->attributes->g_plus_charges,
                'weight' => $cart->attributes->weight,
                'hsn' => $cart->attributes->hsn,
                'gst' => $cart->attributes->gst,
                'type' => $cart->attributes->type,
                'requireddocument' => $cart->attributes->requireddocument,

            )
        ));
        
        $this->updatecartweight($cartid);

        $this->emit('cartcount');

    }

    public function minusqty($cartid)
    {
        $cart = \Cart::get($cartid);

        // you may also want to update a product by reducing its quantity, you do this like so:
        \Cart::update($cartid, array(
            'quantity' => -1, // so if the current product has a quantity of 4, it will subtract 1 and will result to 3
            'attributes' => array(
                'size' => $cart->attributes->size,
                'product_id' => $cart->attributes->product_id,
                'customized_image' => $cart->attributes->customized_image,
                'original_file' => $cart->attributes->original_file,
                'color' => $cart->attributes->color,
                'g_plus' => $cart->attributes->g_plus,
                'cost_per_g' => $cart->attributes->cost_per_g,
                'g_plus_charges' => $cart->attributes->g_plus_charges,
                'weight' => $cart->attributes->weight,
                'hsn' => $cart->attributes->hsn,
                'gst' => $cart->attributes->gst,
                'type' => $cart->attributes->type,
                'requireddocument' => $cart->attributes->requireddocument,
            )
        ));

        $this->updatecartweight($cartid);

        $this->emit('cartcount');
    }

    private function updatecartweight($cartid)
    {
        $cart = \Cart::get($cartid);

        // get product weight
        $product = Product::where('id', $cart->attributes->product_id)->first();

        // if size is a part of sku field then fetch weight from sku row
        if(Config::get('icrm.product_sku.size') == 1)
        {
            // check if color is a part of sku fields then fetch weight from sku row for size + color
            if(Config::get('icrm.product_sku.color') == 1)
            {
                // get sku weight for size + color
                $skuweight = Productsku::where('product_id', $product->id)->where('status', 1)->where('size', $cart->attributes->size)->where('color', $cart->attributes->color)->first();
            }else{
                // get sku weight for size
                $skuweight = Productsku::where('product_id', $product->id)->where('status', 1)->where('size', $cart->attributes->size)->first();
            }

            if(empty($skuweight))
            {
                // sku is empty so fetch default product dimensions
                $cartweight = $product->weight;
            }else{
                // sku weight
                $cartweight = $skuweight->weight;
            }

            
        }else{
            $cartweight = $product->weight;
        }
        
        \Cart::update($cartid, array(
            'attributes' => array(
                'size' => $cart->attributes->size,
                'product_id' => $cart->attributes->product_id,
                'customized_image' => $cart->attributes->customized_image,
                'original_file' => $cart->attributes->original_file,
                'color' => $cart->attributes->color,
                'g_plus' => $cart->attributes->g_plus,
                'cost_per_g' => $cart->attributes->cost_per_g,
                'g_plus_charges' => $cart->attributes->g_plus_charges,
                'weight' => $cartweight * $cart->quantity,
                'hsn' => $cart->attributes->hsn,
                'gst' => $cart->attributes->gst,
                'type' => $cart->attributes->type,
                'requireddocument' => $cart->attributes->requireddocument,
            )
        ));
    }

    public function removecart($cartid)
    {
        \Cart::remove($cartid);

        $this->emit('cartcount');
    }

    public function checkshippingavailability()
    {

        $weight = \Cart::getContent()->sum('attributes.weight');

        if(Config::get('icrm.shipping_provider.shiprocket') == 1)
        {
            $this->shiprocketcheckavailability($weight);

        }

        if(Config::get('icrm.shipping_provider.dtdc') == 1)
        {
            $this->dtdccheckavailability();
        }

        
        
    }

    private function dtdccheckavailability()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://firstmileapi.dtdc.com/dtdc-api/api/custOrder/service/getServiceTypes/$this->pickuppincode/$this->deliverypincode",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
        // "x-access-token: PL2435_trk:a1f86859bcb68b321464e07f159e9747",
        "x-access-token: RO798_trk:bcddd52dd9f433c94376480fca276d9b",
        'Content-Type: application/json',
        ),
        ));


        $response = curl_exec($curl);

        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            // not available
            $this->deliveryavailability = false;
            Session::flash('deliverynotavailable', 'Delivery not available in your area');
            Session::remove('deliverypincode');
            return;
        } else {
            $collection = json_encode(collect($response));
            $collection = json_decode($collection);
            $collection = collect(json_decode($collection[0]));
            // dd($collection);
            if(isset($collection['status']))
            {

                if($collection['status'] == true)
                {
                    
                    $servicelist = $collection['data'];
                    // dd($servicelist);
                    $acceptableservices = ['B2C SMART EXPRESS'];

                    if(in_array('B2C SMART EXPRESS', $servicelist))
                    {
                        // available
                        $this->deliveryavailability = true;

                        /**
                         * Calulate expected delivery date
                         * Today + buffer days + max manufacturing_period
                         */

                        $cartproducts = \Cart::getContent()->pluck('attributes.product_id');
                        
                        if(Config::get('icrm.order_lifecycle.undermanufacturing.feature') == 1)
                        {
                            // get maximum day of the manufacturing period
                            $mpproduct = Product::whereIn('id', $cartproducts)->where('manufacturing_period', '!=', null)->orderBy('manufacturing_period', 'DESC')->first();

                            $bufferdays = Config::get('icrm.shipping_provider.buffer_days') + 1 + $mpproduct->manufacturing_period;
                        }else{
                            $bufferdays = Config::get('icrm.shipping_provider.buffer_days') + 1;
                        }
                        
                        
                        $etd = date('j F, Y', strtotime("+$bufferdays days"));
                        Session::flash('deliveryavailable', 'Expected delivery by '.$etd);
                        Session::put('deliverypincode', $this->deliverypincode);
                        return;
                    }else{
                        // not available
                        $this->deliveryavailability = false;
                        Session::flash('deliverynotavailable', 'Delivery not available in your area');
                        Session::remove('deliverypincode');
                        return;
                    }

                      
                }else{
                    // not available
                    // dd($collection['status']);
                    $this->deliveryavailability = false;
                    Session::flash('deliverynotavailable', 'Delivery not available in your area');
                    Session::remove('deliverypincode');
                    return;
                }
            }else{
                // not available
                $this->deliveryavailability = false;
                Session::flash('deliverynotavailable', 'Delivery not available in your area');
                Session::remove('deliverypincode');
                return;
            }

        }

        $this->deliveryavailability = false;
        Session::flash('deliverynotavailable', 'Delivery not available in your area');
        Session::remove('deliverypincode');
        return;

        return;
    }

    private function shiprocketcheckavailability($weight)
    {
        // https://apidocs.shiprocket.in/?version=latest#29ff5116-0917-41ba-8c82-638412604916
        $pincodeDetails = [
            'pickup_postcode' => $this->pickuppincode,
            'delivery_postcode' => $this->deliverypincode,
            // 1 for Cash on Delivery and 0 for Prepaid orders.
            'cod' => Config::get('icrm.order_methods.cod'),
            'weight' => $weight,
        ];

        // dd($pincodeDetails);
        $token =  Shiprocket::getToken();
        $response =  Shiprocket::courier($token)->checkServiceability($pincodeDetails);

            

        if($response['status'] == 200)
        {
            
            /**
             * Usefull fields:
             * courier_name - Ekart
             * rate - 76.0
             * cod - 1/0
             * etd - Apr 27, 2022
            */ 

            if(Config::get('icrm.shipping_provider.shiprocket_recommendation') == 1)
            {
                if(isset($response['data']['available_courier_companies'][0]))
                {
                    $etd = $response['data']['available_courier_companies'][0]['etd'];
                    $cod = $response['data']['available_courier_companies'][0]['cod'];            
                }
            }else{
                
                $availablecouriercompanies = collect(json_decode($response)->data->available_courier_companies);
                $availablecouriercompaniess = $availablecouriercompanies->sortBy('rate');

                if(isset($availablecouriercompaniess))
                {
                    // dd($availablecouriercompaniess->first());
                    $etd = $availablecouriercompaniess->first()->etd;
                    $cod = $availablecouriercompaniess->first()->cod;
                }
            }

            $this->deliveryavailability = true;
            
            if(Config::get('icrm.order_methods.cod') == 1)
            {
                if($cod == 1)
                {
                    // COD available
                    Session::flash('deliveryavailable', 'Expected delivery by '.$etd.' | Cash on delivery available');
                }else{
                    // COD not available
                    Session::flash('deliveryavailable', 'Expected delivery by '.$etd);
                }
            }else{
                Session::flash('deliveryavailable', 'Expected delivery by '.$etd);
            }

            Session::put('deliverypincode', $this->deliverypincode);


        }else{
            // not available
            $this->deliveryavailability = false;
            Session::put('deliverypincode', $this->deliverypincode);
            Session::flash('deliverynotavailable', 'Delivery not available in your area');
        }
    }

    public function proceedcheckout()
    {
        /**
         * First check if the shipping serviceavailibity is checked
         * If not then redirect back with error message
         */

        if($this->deliveryavailability == false)
        {
            Session::flash('warning', 'Before proceesing please check shipping serviceavailability.');
            return redirect()->route('bag');
        }

        if(empty(Session::get('deliverypincode')))
        {
            Session::flash('warning', 'Before proceesing please check shipping serviceavailability.');
            return redirect()->route('bag');
        }

        return redirect()->route('checkout');
    }
}
