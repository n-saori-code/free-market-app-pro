<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\Checkout\Session as CheckoutSession;
use App\Models\Product;

class StripeService
{
    public function __construct()
    {
        // APIキーの設定（環境ごとに切り替え可能）
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Stripe Checkout セッションを作成する
     *
     * @param Product $product
     * @param string $paymentMethod
     * @param string $successUrl
     * @param string $cancelUrl
     * @return \Stripe\Checkout\Session
     */
    public function createCheckoutSession(Product $product, string $paymentMethod, string $successUrl, string $cancelUrl)
    {
        $sessionData = [
            'line_items' => [[
                'price_data' => [
                    'currency' => 'jpy',
                    'product_data' => ['name' => $product->title],
                    'unit_amount' => $product->price,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ];

        if ($paymentMethod === 'credit_card') {
            $sessionData['payment_method_types'] = ['card'];
        } elseif ($paymentMethod === 'convenience_store') {
            $sessionData['payment_method_types'] = ['konbini'];
        } else {
            throw new \Exception("Unsupported payment method: $paymentMethod");
        }

        return CheckoutSession::create($sessionData);
    }
}
