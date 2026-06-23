<?php

namespace App\Http\Controllers;

use App\Mail\OrderRequestReceived;
use App\Models\Artwork;
use App\Models\OrderRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class OrderRequestController extends Controller
{
    public function create(Artwork $artwork): View
    {
        abort_unless($artwork->is_published, 404);

        return view('orders.create', [
            'artwork' => $artwork->load('category'),
        ]);
    }

    public function store(Request $request, Artwork $artwork): RedirectResponse
    {
        abort_unless($artwork->is_published, 404);

        if (filled($request->input('website'))) {
            return redirect()
                ->route('artworks.show', $artwork)
                ->with('success', 'Спасибо. Заявка отправлена.');
        }

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255', 'required_without:customer_phone'],
            'customer_phone' => ['nullable', 'string', 'max:255', 'required_without:customer_email'],
            'message' => ['nullable', 'string', 'max:2000'],
            'website' => ['nullable', 'max:0'],
        ]);

        $orderRequest = OrderRequest::create([
            'artwork_id' => $artwork->id,
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'] ?? null,
            'customer_phone' => $validated['customer_phone'] ?? null,
            'message' => $validated['message'] ?? null,
            'status' => OrderRequest::STATUS_NEW,
        ]);

        Mail::to(config('gallery.admin_order_email'))->send(new OrderRequestReceived($orderRequest));

        return redirect()
            ->route('artworks.show', $artwork)
            ->with('success', 'Спасибо. Заявка отправлена.');
    }
}
