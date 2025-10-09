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

class OrderController extends Controller
{
    ##商品購入画面の表示
    public function showPurchaseForm($item_id)
    {
        $product = Product::findOrFail($item_id);

        $user = Auth::user();

        $order = Order::where('user_id', Auth::id())
            ->where('product_id', $item_id)
            ->first();

        $address = $order ?? $user->profile;

        return view('purchase', compact('product', 'address'));
    }

    public function showAddressForm($item_id)
    {
        $product = Product::findOrFail($item_id);
        $user = Auth::user();

        $order = Order::where('user_id', $user->id)
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
        $order = Order::firstOrNew(
            [
                'user_id'    => Auth::id(),
                'product_id' => $item_id,
            ]
        );

        if (!$order->exists) {
            $order->postal_code = Auth::user()->profile->postal_code;
            $order->address     = Auth::user()->profile->address;
            $order->building    = Auth::user()->profile->building;
        }

        $order->postal_code = $request->postal_code;
        $order->address     = $request->address;
        $order->building    = $request->building;

        $order->save();

        return redirect()->route('purchase.show', $item_id);
    }

    ##商品購入（Stripe対応）
    public function purchase(PurchaseRequest $request, $item_id)
    {
        $product = Product::findOrFail($item_id);

        if ($product->is_sold) {
            return back();
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $paymentMethod = $request->payment_method;

        // Stripe Checkout セッション作成
        if ($paymentMethod === 'credit_card') {
            $session = CheckoutSession::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'jpy',
                        'product_data' => ['name' => $product->title],
                        'unit_amount' => $product->price,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('purchase.success', ['item_id' => $product->id]),
                'cancel_url' => route('purchase.cancel', ['item_id' => $product->id]),
            ]);
        } elseif ($paymentMethod === 'convenience_store') {
            $session = CheckoutSession::create([
                'payment_method_types' => ['konbini'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'jpy',
                        'product_data' => ['name' => $product->title],
                        'unit_amount' => $product->price,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('purchase.success', ['item_id' => $product->id]),
                'cancel_url' => route('purchase.cancel', ['item_id' => $product->id]),
            ]);
        } else {
            return back()->withErrors(['payment_method' => '支払い方法を選択してください。']);
        }

        $order = Order::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'product_id' => $product->id,
            ],
            [
                'postal_code' => $request->postal_code ?? Auth::user()->profile->postal_code,
                'address'     => $request->address ?? Auth::user()->profile->address,
                'building'    => $request->building ?? Auth::user()->profile->building,
                'payment_method' => $paymentMethod,
            ]
        );

        return redirect($session->url);
    }

    ## 決済成功
    public function success($item_id)
    {
        $product = Product::findOrFail($item_id);
        $product->update(['is_sold' => true]);

        return redirect('/');
    }

    ## 決済キャンセル
    public function cancel($item_id)
    {
        return redirect()->route('purchase.show', $item_id);
    }
}
