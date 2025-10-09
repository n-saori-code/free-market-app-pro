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
            <p class="user__name">{{ $user->name }}</p>
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
                    <p class="sell__tag">Sold</p>
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
        @forelse($purchasedProducts as $product)
        <div class="product__item">
            <a href="{{ route('item.show', $product->id) }}">
                <img class="product__item__image" src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->title }}">
                <div class="product__item__txt">
                    <p class="product__item__title">{{ $product->title }}</p>
                    @if($product->is_sold)
                    <p class="sell__tag">Sold</p>
                    @endif
                </div>
            </a>
        </div>
        @empty
        <p>購入した商品はありません。</p>
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

            // URLを書き換え
            const url = new URL(window.location);
            if (index === 0) {
                url.searchParams.set('page', 'sell');
            } else {
                url.searchParams.set('page', 'buy');
            }
            window.history.pushState({}, '', url);
        }

        // クエリパラメータから初期表示タブを判定
        const params = new URLSearchParams(window.location.search);
        let defaultIndex = 0; // デフォルトは出品タブ

        if (params.get('page') === 'buy') {
            defaultIndex = 1;
        }

        // 初期表示
        showContent(defaultIndex);
        tabs[defaultIndex].checked = true;

        // タブ切り替えイベント
        tabs.forEach((tab, index) => {
            tab.addEventListener('change', () => showContent(index));
        });
    });
</script>

@endsection