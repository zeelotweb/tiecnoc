<?php

namespace App\Http\Controllers\Pay;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MerchController extends Controller
{
    public function checkout(Request $request)
    {
        return $request->user()->checkout(['price_xxx' => 1], [
            'success_url' => route('checkout.success'),
            'cancel_url' => route('checkout.cancel'),
        ]);
    }



        public function cart()
    {
        return view('Platform.cart');
    }

}
