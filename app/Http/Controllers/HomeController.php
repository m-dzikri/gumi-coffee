<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $featuredMenus = Menu::query()
            ->where('is_available', true)
            ->orderBy('id')
            ->limit(3)
            ->get();

        return view('home', [
            'operatingHours' => config('gumi.operating_hours'),
            'location' => config('gumi.location'),
            'estimatedPickupText' => config('gumi.estimated_pickup_text'),
            'categories' => config('gumi.categories', ['Coffee', 'Non-Coffee', 'Pastry']),
            'featuredMenus' => $featuredMenus,
        ]);
    }
}
