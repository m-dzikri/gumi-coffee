<?php

namespace Tests\Feature;

use App\Models\Menu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoundationAndCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_ac1_timezone_is_asia_jakarta_and_homepage_reads_central_operating_hours_config(): void
    {
        $this->assertSame('Asia/Jakarta', config('app.timezone'));

        config([
            'gumi.operating_hours.open' => '09:00',
            'gumi.operating_hours.close' => '21:30',
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSeeText('09:00');
        $response->assertSeeText('21:30');

        $html = (string) $response->getContent();
        $this->assertMatchesRegularExpression('/<main\b[^>]*>(.*?)<\/main>/s', $html);
        preg_match('/<main\b[^>]*>(.*?)<\/main>/s', $html, $matches);
        $mainContent = $matches[1] ?? '';

        $this->assertStringContainsString('JAM OPERASIONAL HARIAN', $mainContent);
        $this->assertStringContainsString('09:00 - 21:30 WIB', $mainContent);
    }

    public function test_ac2_homepage_displays_brand_story_location_and_lihat_menu_cta_to_menus(): void
    {
        config(['gumi.location' => 'Jl. Braga No. 99, Bandung']);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSeeText('Cerita singkat Gumi Coffee');
        $response->assertSeeText('Jl. Braga No. 99, Bandung');
        $response->assertSeeText('Lihat Menu');
        $response->assertSee('href="'.route('menus.index').'"', false);

        $html = (string) $response->getContent();
        $this->assertMatchesRegularExpression('/<main\b[^>]*>(.*?)<\/main>/s', $html);
        preg_match('/<main\b[^>]*>(.*?)<\/main>/s', $html, $matches);
        $mainContent = $matches[1] ?? '';

        $this->assertStringContainsString('Cerita singkat Gumi Coffee', $mainContent);
        $this->assertStringContainsString('Jl. Braga No. 99, Bandung', $mainContent);
        $this->assertStringContainsString('Lihat Menu', $mainContent);
        $this->assertStringContainsString('href="'.route('menus.index').'"', $mainContent);
    }

    public function test_ac5_public_catalog_is_accessible_by_guest_and_renders_categories_rupiah_prices_and_image_fallback(): void
    {
        Menu::create([
            'name' => 'Kopi Susu Gumi',
            'slug' => 'kopi-susu-gumi',
            'category' => 'Coffee',
            'description' => 'Espresso dengan susu segar dan gula aren.',
            'price' => 24000,
            'image_path' => 'images/menus/kopi-susu-gumi.svg',
            'is_available' => true,
        ]);

        Menu::create([
            'name' => 'Croissant Polos',
            'slug' => 'croissant-polos',
            'category' => 'Pastry',
            'description' => 'Croissant mentega renyah.',
            'price' => 22000,
            'image_path' => null,
            'is_available' => true,
        ]);

        $this->assertGuest();

        $response = $this->get('/menus');

        $response->assertOk();
        $response->assertSeeText('Coffee');
        $response->assertSeeText('Pastry');
        $response->assertSeeText('Kopi Susu Gumi');
        $response->assertSeeText('Espresso dengan susu segar dan gula aren.');
        $response->assertSeeText('Rp 24.000');
        $response->assertSee('images/menus/kopi-susu-gumi.svg', false);

        $response->assertSeeText('Croissant Polos');
        $response->assertSeeText('Rp 22.000');
        $response->assertSee('data-testid="menu-image-placeholder"', false);
        $response->assertSeeText('Gambar Belum Tersedia');
    }

    public function test_ac6_unavailable_menu_displays_unavailable_badge_and_has_no_active_order_control(): void
    {
        Menu::create([
            'name' => 'Cold Brew Citrus',
            'slug' => 'cold-brew-citrus',
            'category' => 'Coffee',
            'description' => 'Seduhan dingin 16 jam.',
            'price' => 26000,
            'image_path' => 'images/menus/cold-brew-citrus.svg',
            'is_available' => false,
        ]);

        $response = $this->get('/menus');

        $response->assertOk();
        $response->assertSeeText('Cold Brew Citrus');
        $response->assertSeeText('Habis / Tidak Tersedia');
        $response->assertDontSee('action="/cart/items"', false);
    }

    public function test_ac10_public_pages_use_playful_pop_editorial_palette_sections_semantic_landmarks_and_single_h1(): void
    {
        $this->seed();

        $homeResponse = $this->get('/');
        $homeResponse->assertOk();
        $homeResponse->assertSeeText('GOOD COFFEE.');
        $homeResponse->assertSeeText('ZERO SNOOZE.');
        $homeResponse->assertSeeText('MEET YOUR');
        $homeResponse->assertSeeText('MORNING PEOPLE.');
        $homeResponse->assertSeeText("WHAT'S YOUR");
        $homeResponse->assertSeeText('TYPE?');
        $homeResponse->assertSeeText('LESS GUESS.');
        $homeResponse->assertSeeText('MORE YES.');
        $homeResponse->assertSeeText('SERIOUS ABOUT COFFEE. NOT OURSELVES.');
        $homeResponse->assertSee(route('menus.index').'#kategori-coffee', false);
        $homeResponse->assertSee(route('menus.index').'#kategori-non-coffee', false);
        $homeResponse->assertSee(route('menus.index').'#kategori-pastry', false);

        foreach (['/', '/menus'] as $path) {
            $response = $this->get($path);
            $response->assertOk();

            $html = $response->getContent();
            $this->assertIsString($html);

            $this->assertStringContainsString('<html lang="id">', $html);
            $this->assertStringContainsString('<header', $html);
            $this->assertStringContainsString('<nav', $html);
            $this->assertStringContainsString('<main', $html);
            $this->assertStringContainsString('<footer', $html);

            $this->assertSame(1, substr_count($html, '<h1'), "Expected exactly one <h1> tag on {$path}");

            $this->assertStringContainsString('#b22e3b', $html);
            $this->assertStringContainsString('#fffaee', $html);
            $this->assertStringContainsString('#e9bfd2', $html);
            $this->assertStringContainsString('#020202', $html);
        }
    }
}
