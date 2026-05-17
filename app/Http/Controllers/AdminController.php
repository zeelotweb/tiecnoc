<?php

namespace App\Http\Controllers;

use App\Models\Product; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function administrator()
   {

    return view('Admin.dashboard');
   }


    public function merchandize()
   {
       
    return view('Admin.merchandize');
   }

       public function merchshow($id)
   {

     $products = Product::findorfail($id);
       
    return view('Admin.merchandize_show',  compact('products'));
   }


}
