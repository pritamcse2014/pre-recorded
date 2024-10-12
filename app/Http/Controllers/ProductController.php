<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class ProductController extends Controller
{
    function ProductPage():View {
        return view('pages.dashboard.product-page');
    }
}
