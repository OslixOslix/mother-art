<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function home(): View
    {
        $featuredPool = Artwork::query()
            ->with('category')
            ->published()
            ->featured()
            ->inRandomOrder()
            ->limit(5)
            ->get();

        if ($featuredPool->count() < 5) {
            $excludeIds = $featuredPool->pluck('id');
            $filler = Artwork::query()
                ->with('category')
                ->published()
                ->when($excludeIds->isNotEmpty(), fn ($q) => $q->whereKeyNot($excludeIds))
                ->inRandomOrder()
                ->limit(5 - $featuredPool->count())
                ->get();

            $featuredPool = $featuredPool->concat($filler);
        }

        return view('home.index', [
            'featuredArtworks' => $featuredPool,
            'heroArtworks' => Artwork::query()
                ->published()
                ->latest('published_at')
                ->latest()
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

    public function loadMore(Request $request): JsonResponse
    {
        $query = Artwork::query()
            ->with('category')
            ->published()
            ->latest('published_at')
            ->latest();

        $category = null;

        if ($request->filled('category')) {
            $category = Category::where('slug', $request->input('category'))->firstOrFail();
            abort_unless($category->is_active, 404);
            $query->whereBelongsTo($category);
        }

        $artworks = $query->paginate(24);

        $html = '';
        foreach ($artworks as $artwork) {
            $html .= view('components.stitch.artwork-card', ['artwork' => $artwork])->render();
        }

        return response()->json([
            'html' => $html,
            'hasMore' => $artworks->hasMorePages(),
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
