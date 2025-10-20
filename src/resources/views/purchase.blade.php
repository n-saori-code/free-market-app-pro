@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/purchase.css')}}">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
@endsection

@section('link')
@include('components.header-link')
@endsection

@section('content')
<form action="{{ route('purchase.store', $product->id) }}" method="POST" novalidate>
    @csrf
    <div class="purchase__content">
        <div class="purchase__wrap-01">
            <div class="purchase__product border">
                <div class="purchase__product__image">
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->title }}">
                </div>
                <div class="purchase__product__price">
                    <p class="product-name">{{ $product->title }}</p>
                    <p class="product-price"><span>¥</span>{{ number_format($product->price) }}</p>
                </div>
            </div>

            <div class="purchase__payment">
                <p class="item-title">支払い方法</p>
                <div class="purchase__input--select">
                    <select class="payment_method" name="payment_method" required>
                        <option value="" disabled selected hidden>選択してください</option>
                        <option value="convenience_store" {{ old('payment_method') == 'convenience_store' ? 'selected' : '' }}>コンビニ払い</option>
                        <option value="credit_card" {{ old('payment_method') == 'credit_card' ? 'selected' : '' }}>カード払い</option>
                    </select>

                    <div class="form__error">
                        @error('payment_method')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
            </div>

            <div class="purchase__address">
                <div class="purchase__address__wrap">
                    <p class="item-title">配送先</p>
                    <a href="{{ route('address.show', ['item_id' => $product->id]) }}">変更する</a>
                </div>

                <div class="purchase__item-wrap">
                    @if(session('purchase_address'))
                    <p class="purchase__item-wrap-txt">〒{{ session('purchase_address.postal_code') }}</p>
                    <p class="purchase__item-wrap-txt">
                        {{ session('purchase_address.address') }} {{ session('purchase_address.building') }}
                    </p>

                    <input type="hidden" name="postal_code" value="{{ session('purchase_address.postal_code') }}">
                    <input type="hidden" name="address" value="{{ session('purchase_address.address') }}">
                    <input type="hidden" name="building" value="{{ session('purchase_address.building') }}">
                    @elseif($address)
                    <p class="purchase__item-wrap-txt">〒{{ $address->postal_code }}</p>
                    <p class="purchase__item-wrap-txt">{{ $address->address }} {{ $address->building }}</p>

                    <input type="hidden" name="postal_code" value="{{ $address->postal_code }}">
                    <input type="hidden" name="address" value="{{ $address->address }}">
                    <input type="hidden" name="building" value="{{ $address->building }}">
                    @else
                    <p class="purchase__item-wrap-txt">住所が未登録です</p>
                    @endif

                    <div class="form__error">
                        @error('address')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="purchase__wrap-02">
            <table class="purchase__table">
                <tr>
                    <th>商品代金</th>
                    <td><span>¥</span>{{ number_format($product->price) }}</td>
                </tr>
                <tr>
                    <th>支払い方法</th>
                    <td id="payment-method-display">未選択</td>
                </tr>
            </table>

            @if($product->is_sold)
            <button class="purchase__btn" disabled>Sold</button>
            @else
            <button type="submit" class="purchase__btn">購入する</button>
            @endif
        </div>
    </div>
</form>
@endsection


@section('script')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const select = document.querySelector(".payment_method");
        const display = document.getElementById("payment-method-display");

        const mapping = {
            "convenience_store": "コンビニ払い",
            "credit_card": "カード払い"
        };

        select.addEventListener("change", function() {
            const selected = select.value;
            display.textContent = mapping[selected] ?? "未選択";
        });
    });
</script>

@endsection