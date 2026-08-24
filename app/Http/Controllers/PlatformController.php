<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;

class PlatformController extends Controller
{
    public function displaymerch($id)
    {
        return view('Platform.show-merch', ['slug' => $id]);
    }

    public function merchall()
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

    public function sale()
    {
        return view('Platform.sale');
    }

    public function cart()
    {
        return view('Platform.cart');
    }

    public function favorites()
    {
        return view('Platform.favorites');
    }

    public function orderManifestDownload($orderNumber)
    {
        $order = Order::with('items')->where('order_number', $orderNumber)->firstOrFail();

        $pdf = Pdf::loadView('pdf.manifest', ['order' => $order]);

        return $pdf->download("manifest-{$order->order_number}.pdf");
    }
}
