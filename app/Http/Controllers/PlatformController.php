<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PlatformController extends Controller
{

public function displaymerch($id)
{
    // We take 'the-essentials' and pass it to the blade as 'slug'
    return view('Platform.show-merch', ['slug' => $id]);
}


public function merchall()
{

//$affected = DB::table('users')
  //  ->where('id', 1)
    //->update(['role' => 'super_admin']);

    // We take 'the-essentials' and pass it to the blade as 'slug'
    return view('Platform.All-merch');
}






    public function all()
    {
        return view('Platform.All-merch');
    }




    public function male()
    {
        return view('Platform.male');
    }


    public function female()
    {
        return view('Platform.female');
    }



    public function unisex()
    {
        return view('Platform.unisex');
    }


    public function wish()
    {
        return view('Platform.wishlist');
    }

    public function pulse()
    {
        return view('Platform.pulse');
    }





}
