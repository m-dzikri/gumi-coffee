<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(): View
    {
        $categories = config('gumi.categories', ['Coffee', 'Non-Coffee', 'Pastry']);
        $allMenus = Menu::query()
            ->orderBy('id')
            ->get();

        $menusByCategory = [];
        foreach ($categories as $category) {
            $items = $allMenus->where('category', $category)->values();
            if ($items->isNotEmpty()) {
                $menusByCategory[$category] = $items;
            }
        }

        foreach ($allMenus->groupBy('category') as $category => $items) {
            if (! array_key_exists($category, $menusByCategory)) {
                $menusByCategory[$category] = $items->values();
            }
        }

        return view('menus.index', [
            'categories' => $categories,
            'menusByCategory' => $menusByCategory,
            'operatingHours' => config('gumi.operating_hours'),
            'estimatedPickupText' => config('gumi.estimated_pickup_text'),
            'location' => config('gumi.location'),
        ]);
    }
}
