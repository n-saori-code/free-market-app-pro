<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    ##取引評価
    public function store(Request $request, Order $order)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $user = Auth::user();

        $reviewedId = $order->buyer_id === $user->id
            ? $order->seller_id
            : $order->buyer_id;

        Review::create([
            'order_id'    => $order->id,
            'reviewer_id' => $user->id,
            'reviewed_id' => $reviewedId,
            'rating'      => $request->rating,
        ]);

        return redirect()->route('item.index');
    }
}
