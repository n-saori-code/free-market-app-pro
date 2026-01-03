<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Message;
use App\Models\Review;
use Illuminate\Http\Request;
use App\Http\Requests\StoreMessageRequest;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    ##取引画面の表示
    public function show(Order $order)
    {
        $user = Auth::user();

        // 自分が関係する取引かチェック
        abort_unless(
            $order->buyer_id === $user->id || $order->seller_id === $user->id,
            403
        );

        Message::where('order_id', $order->id)
            ->where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);

        // 相手ユーザー判定
        $partner = $order->buyer_id === $user->id
            ? $order->seller
            : $order->buyer;

        // 左サイドバー用：取引中の商品一覧（最新メッセージ順）
        $tradingOrders = Order::with('product')
            ->withMax(['messages' => function ($q) {
                $q->whereNull('deleted_at');
            }], 'created_at')
            ->where(function ($q) use ($user) {
                $q->where('buyer_id', $user->id)
                    ->orWhere('seller_id', $user->id);
            })
            ->where('status', Order::STATUS_IN_CHAT)
            ->orderByDesc('messages_max_created_at')
            ->get();

        // メッセージ取得（プロフィール込み）
        $messages = $order->messages()
            ->with(['sender.profile'])
            ->whereNull('deleted_at')
            ->orderBy('created_at')
            ->get();

        $hasReviewed = $order->reviews()
            ->where('reviewer_id', $user->id)
            ->exists();

        $shouldShowReviewModal =
            $order->status === Order::STATUS_COMPLETED &&
            ! $hasReviewed;

        return view('messages.show', compact(
            'order',
            'partner',
            'messages',
            'tradingOrders',
            'user',
            'shouldShowReviewModal'
        ));
    }

    ##メッセージ送信
    public function store(StoreMessageRequest $request, Order $order)
    {
        $user = Auth::user();

        abort_unless(
            $order->buyer_id === $user->id || $order->seller_id === $user->id,
            403
        );

        $receiverId = $order->buyer_id === $user->id
            ? $order->seller_id
            : $order->buyer_id;

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('messages', 'public');
        }

        Message::create([
            'order_id'    => $order->id,
            'sender_id'   => $user->id,
            'receiver_id' => $receiverId,
            'content'     => $request->content,
            'image'  => $imagePath,
        ]);

        return redirect()->route('messages.show', $order->id);
    }

    ##メッセージ編集
    public function update(Request $request, Order $order, Message $message)
    {
        $user = Auth::user();

        abort_unless($message->sender_id === $user->id, 403);

        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $message->update([
            'content' => $request->content,
        ]);

        return redirect()->route('messages.show', $order->id);
    }

    ##メッセージ削除
    public function destroy(Order $order, Message $message)
    {
        $user = Auth::user();

        abort_unless($message->sender_id === $user->id, 403);

        $message->delete(); // SoftDelete

        return redirect()->route('messages.show', $order->id);
    }
}
