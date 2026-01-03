<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Message;
use App\Models\Order;
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

        // 購入した商品（完了済み含む）
        $purchasedOrders = Order::with('product')
            ->where('buyer_id', $user->id)
            ->where('status', Order::STATUS_COMPLETED)
            ->latest()
            ->get();

        // 取引中の商品（購入者・出品者 両方）
        $tradingOrders = Order::with('product')
            ->withCount([
                'messages as unread_messages_count' => function ($q) use ($user) {
                    $q->where('receiver_id', $user->id)
                        ->whereNull('read_at');
                }
            ])
            ->where(function ($q) use ($user) {
                $q->where('buyer_id', $user->id)
                    ->orWhere('seller_id', $user->id);
            })
            ->where(function ($q) use ($user) {
                $q->where('status', Order::STATUS_IN_CHAT)

                    // 出品者がまだ評価していない取引も含める
                    ->orWhere(function ($q) use ($user) {
                        $q->where('status', Order::STATUS_COMPLETED)
                            ->where('seller_id', $user->id)
                            ->whereDoesntHave('reviews', function ($q) use ($user) {
                                $q->where('reviewer_id', $user->id);
                            });
                    });
            })
            ->orderByDesc(
                Message::select('created_at')
                    ->whereColumn('order_id', 'orders.id')
                    ->latest()
                    ->limit(1)
            )
            ->get();

        // 未読メッセージ数
        $unreadMessageCount = Message::where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return view('profile', compact(
            'user',
            'listedProducts',
            'purchasedOrders',
            'tradingOrders',
            'unreadMessageCount'
        ));
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
