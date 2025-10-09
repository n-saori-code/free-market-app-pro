<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Requests\ProfileRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    ##マイページの表示
    public function mypage()
    {
        $user = Auth::user();

        // 出品した商品
        $listedProducts = $user->products()->latest()->get();

        // 購入した商品
        $purchasedProducts = $user->orders()->with('product')->get()->map(function ($order) {
            return $order->product;
        });

        return view('profile', compact('user', 'listedProducts', 'purchasedProducts'));
    }

    ##プロフィール編集画面の表示
    public function edit()
    {
        $user = Auth::user();
        return view('profile-edit', compact('user'));
    }

    // プロフィール更新
    public function update(ProfileRequest $request)
    {
        $user = Auth::user();

        $profile = $user->profile ?? $user->profile()->create([]);

        // フォームの値をセット
        $profile->postal_code = $request->postal_code;
        $profile->address = $request->address;
        $profile->building = $request->building;

        // 画像アップロードがある場合
        if ($request->hasFile('profile_image')) {
            $profile->profile_image = $request->file('profile_image')->store('profile_images', 'public');
        }

        $profile->save();

        $user->name = $request->name;
        $user->save();

        return redirect('/?tab=mylist');
    }
}
