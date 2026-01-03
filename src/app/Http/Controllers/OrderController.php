<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Requests\PurchaseRequest;
use App\Http\Requests\AddressRequest;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\Checkout\Session as CheckoutSession;
use App\Services\StripeService;
use App\Mail\OrderCompletedMail;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    ##商品購入画面の表示
    public function showPurchaseForm($item_id)
    {
        $product = Product::findOrFail($item_id);

        $user = Auth::user();

        $order = Order::where('buyer_id', Auth::id())
            ->where('product_id', $item_id)
            ->first();

        $address = $order
            ?? session('purchase_address')
            ?? $user->profile;

        return view('purchase', compact('product', 'address'));
    }

    #住所変更画面の表示
    public function showAddressForm($item_id)
    {
        $product = Product::findOrFail($item_id);
        $user = Auth::user();

        $order = Order::where('buyer_id', $user->id)
            ->where('product_id', $item_id)
            ->first();

        $postal_code = $order->postal_code ?? $user->profile->postal_code ?? '';
        $address     = $order->address ?? $user->profile->address ?? '';
        $building    = $order->building ?? $user->profile->building ?? '';

        return view('address', compact('product', 'item_id', 'postal_code', 'address', 'building'));
    }

    ##住所変更
    public function updateAddress(AddressRequest $request, $item_id)
    {
        $order = Order::where('buyer_id', Auth::id())
            ->where('product_id', $item_id)
            ->first();

        if ($order) {
            $order->postal_code = $request->postal_code;
            $order->address     = $request->address;
            $order->building    = $request->building;
            $order->save();
        } else {
            session(['purchase_address' => $request->only('postal_code', 'address', 'building')]);
        }

        return redirect()->route('purchase.show', $item_id);
    }

    ##商品購入（Stripe対応）
    public function purchase(PurchaseRequest $request, $item_id, StripeService $stripe)
    {
        $product = Product::findOrFail($item_id);

        if ($product->is_sold) {
            return back();
        }

        $session = $stripe->createCheckoutSession(
            $product,
            $request->payment_method,
            route('purchase.success', ['item_id' => $product->id]),
            route('purchase.cancel', ['item_id' => $product->id])
        );

        return redirect($session->url);
    }

    ## 決済成功
    public function success($item_id)
    {
        $product = Product::findOrFail($item_id);

        if (!$product->is_sold) {
            $product->update(['is_sold' => true]);

            $address = session('purchase_address') ?? Auth::user()->profile;

            $order = Order::updateOrCreate(
                [
                    'buyer_id'   => Auth::id(),
                    'product_id' => $product->id,
                ],
                [
                    'seller_id' => $product->user_id,
                    'postal_code' => is_array($address)
                        ? $address['postal_code']
                        : $address->postal_code,
                    'address' => is_array($address)
                        ? $address['address']
                        : $address->address,
                    'building' => is_array($address)
                        ? $address['building']
                        : $address->building,
                    'payment_method' => 'stripe',
                    'status' => Order::STATUS_IN_CHAT,
                ]
            );

            session()->forget('purchase_address');
        } else {
            $order = Order::where('buyer_id', Auth::id())
                ->where('product_id', $product->id)
                ->firstOrFail();
        }

        // 取引チャット画面へ
        return redirect()->route('messages.show', $order->id);
    }

    ## 決済キャンセル
    public function cancel($item_id)
    {
        return redirect()->route('purchase.show', $item_id);
    }

    ##チャットの取引完了
    public function complete(Order $order)
    {
        $user = Auth::user();

        abort_unless($order->buyer_id === $user->id || $order->seller_id === $user->id, 403);

        $order->status = Order::STATUS_COMPLETED;
        $order->save();

        Mail::to($order->seller->email)->send(new OrderCompletedMail($order));

        return redirect()->route('messages.show', $order->id);
    }
}
