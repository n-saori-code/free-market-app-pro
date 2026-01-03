@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/profile.css')}}">
@endsection

@section('link')
@include('components.header-link')
@endsection

@section('content')
<div class="user__content">
    <div class="user__item">
        <div class="user__info">
            @if($user->profile && $user->profile->profile_image)
            <div class="user__image">
                <img src="{{ asset('storage/'.$user->profile->profile_image) }}"
                    alt="ユーザー写真">
            </div>
            @else
            <div class="user__image-circle"></div>
            @endif

            <div>
                <p class="user__name">{{ $user->name }}</p>

                <!-- 星評価 -->
                @if($user->average_rating)
                <div class="user__rating" aria-label="評価 {{ $user->average_rating }} / 5">
                    @for ($i = 1; $i <= 5; $i++)
                        <span class="star {{ $i <= $user->average_rating ? 'is-active' : '' }}">★</span>
                        @endfor
                </div>
                @endif
            </div>
        </div>
        <div class="user__btn">
            <a href="{{ route('profile.edit') }}">プロフィールを編集</a>
        </div>
    </div>
</div>

<ul class="product__tab">
    <li class="product__list">
        <input type="radio" name="tab" id="tab1" checked>
        <label for="tab1">出品した商品</label>
    </li>

    <li class="product__list">
        <input type="radio" name="tab" id="tab2">
        <label for="tab2">購入した商品</label>
    </li>

    <li class="product__list">
        <input type="radio" name="tab" id="tab3">
        <label for="tab3">
            取引中の商品
            @if(($unreadMessageCount ?? 0) > 0)
            <span class="badge">{{ $unreadMessageCount }}</span>
            @endif
        </label>
    </li>

</ul>

<div class="product__content">
    <!-- 出品した商品 -->
    <div class="tab-content" id="content1">
        @forelse($listedProducts as $product)
        <div class="product__item">
            <a href="{{ route('item.show', $product->id) }}">
                <img class="product__item__image" src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->title }}">
                <div class="product__item__txt">
                    <p class="product__item__title">{{ $product->title }}</p>
                    @if($product->is_sold)
                    <p class="sell__tag">SOLD</p>
                    @endif
                </div>
            </a>
        </div>
        @empty
        <p>出品した商品はありません。</p>
        @endforelse
    </div>

    <!-- 購入した商品 -->
    <div class="tab-content" id="content2">
        @forelse($purchasedOrders as $order)
        @php $product = $order->product; @endphp
        <div class="product__item">
            <a href="{{ route('item.show', $product->id) }}">
                <img class="product__item__image"
                    src="{{ asset('storage/' . $product->image) }}">
                <div class="product__item__txt">
                    <p class="product__item__title">{{ $product->title }}</p>
                    <p class="sell__tag">SOLD</p>
                </div>
            </a>
        </div>
        @empty
        <p>購入した商品はありません。</p>
        @endforelse
    </div>

    <!-- 取引中の商品 -->
    <div class="tab-content" id="content3">
        @forelse($tradingOrders as $order)
        @php
        $product = $order->product;
        $unreadCount = $order->unread_messages_count ?? 0;
        @endphp

        <div class="product__item">
            <a href="{{ route('messages.show', $order->id) }}" class="product__link">

                <div class="product__image-wrapper">
                    <img class="product__item__image"
                        src="{{ asset('storage/' . $product->image) }}">

                    @if($unreadCount > 0)
                    <span class="product-badge">{{ $unreadCount }}</span>
                    @endif
                </div>

                <div class="product__item__txt">
                    <p class="product__item__title">{{ $product->title }}</p>
                </div>

            </a>
        </div>
        @empty
        <p>取引中の商品はありません。</p>
        @endforelse
    </div>
</div>

@endsection


@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabs = document.querySelectorAll('.product__tab input');
        const contents = document.querySelectorAll('.tab-content');

        function showContent(index) {
            contents.forEach((content, i) => {
                content.style.display = (i === index) ? 'grid' : 'none';
            });

            const url = new URL(window.location);
            const pages = ['sell', 'buy', 'trading'];
            url.searchParams.set('page', pages[index]);
            window.history.pushState({}, '', url);
        }

        const params = new URLSearchParams(window.location.search);
        let defaultIndex = 0;

        switch (params.get('page')) {
            case 'sell':
                defaultIndex = 0;
                break;
            case 'buy':
                defaultIndex = 1;
                break;
            case 'trading':
                defaultIndex = 2;
                break;
        }

        showContent(defaultIndex);
        tabs[defaultIndex].checked = true;

        tabs.forEach((tab, index) => {
            tab.addEventListener('change', () => showContent(index));
        });
    });
</script>

@endsection