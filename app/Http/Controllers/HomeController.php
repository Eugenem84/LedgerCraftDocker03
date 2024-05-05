<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function showStatistic()
    {
        return view('statistic');
    }

    public function showOrderMake()
    {
        return view('order');
    }

    public function showCatalog()
    {
        return view('catalog');
    }

    public function showHistory()
    {
        return view('history');
    }
}
