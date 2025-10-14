@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css')}}">
@endsection

@section('link')
@include('components.header-link')
@endsection

@section('content')
<ul class="product__tab">
    <li class="product__list">
        <input type="radio" name="tab" id="tab1" checked>
        <label for="tab1">おすすめ</label>
    </li>

    <li class="product__list">
        <input type="radio" name="tab" id="tab2">
        @auth
        <label for="tab2">マイリスト</label>
        @endauth

        @guest
        <label for="tab2">
            <label for="tab2">マイリスト</label>
        </label>
        @endguest
    </li>
</ul>

<div class="product__content">
    <!-- おすすめ -->
    <div class="tab-content" id="content1">
        @foreach($products as $product)
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
        @endforeach
    </div>

    <!-- マイリスト -->
    <div class="tab-content" id="content2">
        @php
        $favorites = Auth::check() ? ($favoriteProducts ?? collect()) : collect();
        @endphp

        @if(Auth::check())
        @if($favorites->isEmpty())
        <p>いいねした商品はありません。</p>
        @else
        @foreach($favorites as $product)
        <div class="product__item">
            <a href="{{ route('item.show', $product->id) }}">
                <div class="product__image">
                    <img class="product__item__image" src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->title }}">
                </div>
                <div class="product__item__txt">
                    <p class="product__item__title">{{ $product->title }}</p>
                    @if($product->is_sold)
                    <p class="sell__tag">SOLD</p>
                    @endif
                </div>
            </a>
        </div>
        @endforeach
        @endif
        @endif
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

            // URL書き換え
            const url = new URL(window.location);
            if (index === 0) {
                url.searchParams.delete('tab');
            } else {
                url.searchParams.set('tab', 'mylist');
            }
            window.history.pushState({}, '', url);
        }

        // デフォルトはおすすめ
        let defaultIndex = 0;

        // クエリパラメータから判定
        const params = new URLSearchParams(window.location.search);
        if (params.get('tab') === 'mylist') {
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