<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminMenuController extends Controller
{
    public function index(): View
    {
        $menus = Menu::query()
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $categories = config('gumi.categories', ['Coffee', 'Non-Coffee', 'Pastry']);

        return view('admin.menus.index', [
            'menus' => $menus,
            'categories' => $categories,
        ]);
    }

    public function create(): View
    {
        $categories = config('gumi.categories', ['Coffee', 'Non-Coffee', 'Pastry']);

        return view('admin.menus.create', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateMenu($request);

        $slug = $this->generateUniqueSlug($validated['name']);
        $isAvailable = array_key_exists('is_available', $validated)
            ? (bool) $validated['is_available']
            : true;

        $menu = Menu::query()->create([
            'name' => $validated['name'],
            'slug' => $slug,
            'category' => $validated['category'],
            'description' => $validated['description'] ?? null,
            'price' => (int) $validated['price'],
            'image_path' => ! empty($validated['image_path']) ? trim($validated['image_path']) : null,
            'is_available' => $isAvailable,
        ]);

        return redirect()
            ->route('admin.menus.index')
            ->with('status', "Menu {$menu->name} berhasil ditambahkan ke katalog.");
    }

    public function edit(Menu $menu): View
    {
        $categories = config('gumi.categories', ['Coffee', 'Non-Coffee', 'Pastry']);

        return view('admin.menus.edit', [
            'menu' => $menu,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, Menu $menu): RedirectResponse
    {
        $validated = $this->validateMenu($request);

        $slug = $menu->name === $validated['name'] && $menu->slug !== ''
            ? $menu->slug
            : $this->generateUniqueSlug($validated['name'], $menu->id);

        $isAvailable = array_key_exists('is_available', $validated)
            ? (bool) $validated['is_available']
            : $menu->is_available;

        $menu->update([
            'name' => $validated['name'],
            'slug' => $slug,
            'category' => $validated['category'],
            'description' => $validated['description'] ?? null,
            'price' => (int) $validated['price'],
            'image_path' => ! empty($validated['image_path']) ? trim($validated['image_path']) : null,
            'is_available' => $isAvailable,
        ]);

        return redirect()
            ->route('admin.menus.index')
            ->with('status', "Menu {$menu->name} berhasil diperbarui.");
    }

    public function toggleAvailability(Menu $menu): RedirectResponse
    {
        $menu->update([
            'is_available' => ! $menu->is_available,
        ]);

        $statusLabel = $menu->is_available ? 'Tersedia' : 'Tidak Tersedia';

        return redirect()
            ->route('admin.menus.index')
            ->with('status', "Status ketersediaan {$menu->name} diubah menjadi {$statusLabel}.");
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        $menuName = $menu->name;
        $menu->delete();

        return redirect()
            ->route('admin.menus.index')
            ->with('status', "Menu {$menuName} berhasil dihapus dari katalog.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validateMenu(Request $request): array
    {
        $categories = config('gumi.categories', ['Coffee', 'Non-Coffee', 'Pastry']);

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', Rule::in($categories)],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'integer', 'min:1000'],
            'image_path' => ['nullable', 'string', 'max:2048'],
            'is_available' => ['sometimes', 'boolean'],
        ]);
    }

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        if ($baseSlug === '') {
            $baseSlug = 'menu';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            Menu::query()
                ->where('slug', $slug)
                ->when($ignoreId !== null, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
