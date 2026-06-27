<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use App\Models\Category;
use Illuminate\Contracts\View\View;

class GalleryController extends Controller
{
    public function home(): View
    {
        return view('home.index', [
            'featuredArtworks' => Artwork::query()
                ->with('category')
                ->published()
                ->latest('published_at')
                ->latest()
                ->limit(6)
                ->get(),
            'heroArtworks' => Artwork::query()
                ->published()
                ->latest('published_at')
                ->latest()
                ->limit(10)
                ->get(),
        ]);
    }

    public function index(): View
    {
        return view('gallery.index', [
            'categories' => $this->categories(),
            'activeCategory' => null,
            'artworks' => Artwork::query()
                ->with('category')
                ->published()
                ->latest('published_at')
                ->latest()
                ->paginate(24),
        ]);
    }

    public function category(Category $category): View
    {
        abort_unless($category->is_active, 404);

        return view('gallery.index', [
            'categories' => $this->categories(),
            'activeCategory' => $category,
            'artworks' => Artwork::query()
                ->with('category')
                ->whereBelongsTo($category)
                ->published()
                ->latest('published_at')
                ->latest()
                ->paginate(24),
        ]);
    }

    public function show(Artwork $artwork): View
    {
        abort_unless($artwork->is_published, 404);

        $artwork->load('category');

        return view('gallery.show', [
            'categories' => $this->categories(),
            'artwork' => $artwork,
            'previousArtwork' => $artwork->previousPublished(),
            'nextArtwork' => $artwork->nextPublished(),
            'relatedArtworks' => Artwork::query()
                ->with('category')
                ->published()
                ->when($artwork->category_id, fn ($query) => $query->where('category_id', $artwork->category_id))
                ->whereKeyNot($artwork->id)
                ->latest('published_at')
                ->latest()
                ->limit(6)
                ->get()
                ->prepend($artwork),
        ]);
    }

    private function categories()
    {
        return Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}
