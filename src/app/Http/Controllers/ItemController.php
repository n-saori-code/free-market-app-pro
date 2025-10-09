<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Condition;
use Illuminate\Http\Request;
use App\Http\Requests\ExhibitionRequest;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    // 一覧表示
    public function index()
    {
        $userId = Auth::id();

        $products = Product::where('user_id', '!=', $userId)->get();

        $favoriteProducts = $this->getFavoriteProducts();

        return view('index', compact('products', 'favoriteProducts'));
    }

    // 検索メソッド
    public function search(Request $request)
    {
        $keyword = $request->keyword;
        $userId = Auth::id();

        // 商品検索
        $products = Product::query()
            ->when($keyword, function ($query) use ($keyword) {
                $query->keywordSearch($keyword);
            })
            ->when($userId, function ($query, $userId) {
                $query->where('user_id', '!=', $userId);
            })
            ->get();

        // お気に入り検索
        $favoriteProducts = collect();
        if (Auth::check()) {
            $favoriteProducts = Auth::user()->favoriteProducts;

            // キーワードがある場合は絞り込み
            if (!empty($keyword)) {
                $favoriteProducts = $favoriteProducts->filter(function ($product) use ($keyword) {
                    return str_contains($product->title, $keyword);
                });
            }
        }

        return view('index', compact('products', 'favoriteProducts', 'keyword'));
    }

    // ログインユーザーのお気に入り取得
    private function getFavoriteProducts()
    {
        if (Auth::check()) {
            return Auth::user()->favoriteProducts;
        }

        return collect();
    }

    // 商品詳細画面の表示
    public function show($item_id)
    {
        $product = Product::with([
            'comments.user.profile',
            'favoritedBy'
        ])->findOrFail($item_id);

        return view('item', compact('product'));
    }

    // 商品出品画面の表示
    public function create()
    {
        $categories = Category::all();
        $conditions = Condition::all();

        return view('sell', compact('categories', 'conditions'));
    }

    // 商品出品処理
    public function store(ExhibitionRequest $request)
    {
        $imagePath = $request->file('profile_image')->store('products', 'public');

        $product = Product::create([
            'user_id' => Auth::id(),
            'condition_id' => $request->condition,
            'image' => $imagePath,
            'title' => $request->title,
            'brand' => $request->brand,
            'description' => $request->description,
            'price' => $request->price,
            'is_sold' => false,
        ]);

        $product->categories()->attach($request->category_id);

        return redirect()->route('mypage')->with('success', '商品を出品しました！');
    }
}
