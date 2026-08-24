<?php

namespace App\Http\Controllers;

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

    public function orders()
    {
        return view('Admin.orders');
    }

    public function team()
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        return view('Admin.team');
    }

    public function reports()
    {
        return view('Admin.reports');
    }

}
