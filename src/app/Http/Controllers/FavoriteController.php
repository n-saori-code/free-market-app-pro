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

        // リレーションを使って登録（重複を防ぐ）
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
