<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@gumicoffee.test'],
            [
                'name' => 'Admin Gumi',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'customer@gumicoffee.test'],
            [
                'name' => 'Pelanggan Gumi',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'email_verified_at' => now(),
            ]
        );

        $menus = [
            [
                'name' => 'Kopi Susu Gumi',
                'slug' => 'kopi-susu-gumi',
                'category' => 'Coffee',
                'description' => 'Racikan house blend espresso Jawa Barat dengan susu segar dingin dan gula aren organik.',
                'price' => 24000,
                'image_path' => 'images/menus/kopi-susu-gumi.svg',
                'is_available' => true,
            ],
            [
                'name' => 'Americano Braga',
                'slug' => 'americano-braga',
                'category' => 'Coffee',
                'description' => 'Double shot espresso arabika Pangalengan dengan karakter floral bersih dan akhir rasa kakao.',
                'price' => 20000,
                'image_path' => 'images/menus/americano-braga.svg',
                'is_available' => true,
            ],
            [
                'name' => 'Salted Caramel Latte',
                'slug' => 'salted-caramel-latte',
                'category' => 'Coffee',
                'description' => 'Perpaduan espresso penuh, susu tekstur lembut, dan saus karamel garam laut buatan dapur Gumi.',
                'price' => 28000,
                'image_path' => 'images/menus/salted-caramel-latte.svg',
                'is_available' => true,
            ],
            [
                'name' => 'Cold Brew Citrus',
                'slug' => 'cold-brew-citrus',
                'category' => 'Coffee',
                'description' => 'Seduhan dingin 16 jam dengan sentuhan kulit jeruk lokal segar dan sensasi dingin menyegarkan.',
                'price' => 26000,
                'image_path' => 'images/menus/cold-brew-citrus.svg',
                'is_available' => false,
            ],
            [
                'name' => 'Matcha Oat Kyoto',
                'slug' => 'matcha-oat-kyoto',
                'category' => 'Non-Coffee',
                'description' => 'Bubuk teh hijau ceremonial grade dari Uji yang dikocok hangat lalu dituang di atas susu gandum.',
                'price' => 29000,
                'image_path' => 'images/menus/matcha-oat-kyoto.svg',
                'is_available' => true,
            ],
            [
                'name' => 'Dark Chocolate Valrhona',
                'slug' => 'dark-chocolate-valrhona',
                'category' => 'Non-Coffee',
                'description' => 'Cokelat hitam pekat 70 persen kakao dengan tekstur kental dan rasa pahit-manis seimbang.',
                'price' => 27000,
                'image_path' => 'images/menus/dark-chocolate-valrhona.svg',
                'is_available' => true,
            ],
            [
                'name' => 'Butter Croissant Panggang',
                'slug' => 'butter-croissant-panggang',
                'category' => 'Pastry',
                'description' => 'Pastry berlapis mentega Prancis yang dipanggang setiap pagi hingga renyah di luar dan lembut di dalam.',
                'price' => 22000,
                'image_path' => 'images/menus/butter-croissant-panggang.svg',
                'is_available' => true,
            ],
        ];

        foreach ($menus as $menu) {
            Menu::updateOrCreate(
                ['slug' => $menu['slug']],
                $menu
            );
        }
    }
}
