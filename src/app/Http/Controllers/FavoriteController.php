<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    // お気に入り登録
    public function favorite($id)
    {
        $user = Auth::user();
        $product = Product::findOrFail($id);

        if ($product->user_id === $user->id) {
            return response()->json([
                'message' => '自分の商品にはいいねできません'
            ], 403);
        }

        $user->favoriteProducts()->syncWithoutDetaching([$product->id]);

        return response()->json([
            'status' => 'favorited',
            'count' => $product->favoritedBy()->count(),
        ]);
    }

    // お気に入り解除
    public function unfavorite($id)
    {
        $user = Auth::user();
        $product = Product::findOrFail($id);

        // リレーションを使って解除
        $user->favoriteProducts()->detach($product->id);

        return response()->json([
            'status' => 'unfavorited',
            'count' => $product->favoritedBy()->count(),
        ]);
    }
}
