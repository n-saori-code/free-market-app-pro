@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/item.css')}}">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
@endsection

@section('link')
@include('components.header-link')
@endsection

@section('content')
<div class="item__content">
    <div class="item__image">
        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->title }}">
    </div>

    <div class="item__detail">
        <h2 class="item__title">{{ $product->title }}</h2>
        <p class="item__brand">{{ $product->brand ?? 'ブランドなし' }}</p>
        <p class="item__price">¥<span>{{ number_format($product->price) }}</span>(税込)</p>
        <div class="item__icon">
            <div class="action-item">
                @php
                $isOwner = Auth::check() && $product->user_id === Auth::id();
                $isFavorited = $product->favoritedBy->contains('id', auth()->id());
                @endphp
                <button class="favorite-btn"
                    data-id="{{ $product->id }}"
                    data-favorited="{{ $isFavorited ? 'true' : 'false' }}"
                    data-auth="{{ Auth::check() ? 'true' : 'false' }}"
                    @if($isOwner) disabled @endif>
                    <img src="{{ asset($isFavorited ? 'images/icon-star-filled.png' : 'images/icon-star.png') }}" alt="star" class="action-icon">
                </button>
                <span class="count">{{ $product->favoritedBy->count() }}</span>
            </div>
            <div class="action-item">
                <img src="{{ asset('images/icon-comment.png') }}" alt="comment" class="action-icon">
                <span class="count">{{ $product->comments->count() }}</span>
            </div>
        </div>

        @if($product->is_sold)
        <button class="item__btn item__btn--sold" disabled>SOLD</button>
        @elseif(Auth::check() && $product->user_id === Auth::id())
        <button class="item__btn item__btn--disabled" disabled>出品者のため購入できません</button>
        @else
        <form action="{{ route('purchase.show', ['item_id' => $product->id]) }}" method="get" class="item__form">
            <button type="submit" class="item__btn">購入手続きへ</button>
        </form>
        @endif

        <div class="item__description">
            <h3 class="item__head">商品説明</h3>
            <p class="item__description__txt">{{ $product->description }}</p>
        </div>

        <div class="item__information">
            <h3 class="item__head">商品の情報</h3>
            <table class="item__information__table">
                <tr>
                    <th>カテゴリー</th>
                    <td class="category-tags">
                        @foreach($product->categories as $category)
                        <span class="category-tag">{{ $category->category_name }}</span>
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <th>商品の状態</th>
                    <td>{{ $product->condition->condition_name }}</td>
                </tr>
            </table>
        </div>

        <div class="item__comment">
            <h3 class="item__comment_title">コメント({{ $product->comments->count() }})</h3>

            @foreach($product->comments as $comment)
            <div class="item__account">
                <div class="account__list">
                    @if($comment->user->profile && $comment->user->profile->profile_image)
                    <div class="item__account__image">
                        <img src="{{ asset('storage/'.$comment->user->profile->profile_image) }}" alt="">
                    </div>
                    @else
                    <div class="item__account__circle"></div>
                    @endif
                    <p class="item__user-name">{{ $comment->user->name }}</p>
                </div>
                <div class="comment__content">
                    <p class="item__user-comment">{{ $comment->content }}</p>
                </div>
            </div>
            @endforeach

            @if($product->is_sold)
            <div class="sold-out-comment">
                <p class="sold-out-message">売り切れの為、コメントできません。</p>
                <button class="form__button-submit sold-btn" disabled>コメントを送信する</button>
            </div>
            @else
            <form action="{{ route('comments.store', $product->id) }}" method="POST" class="item__comment__form">
                @csrf
                <div class="form__group">
                    <div class="form__group-title">
                        <span class="form__label--item">商品へのコメント</span>
                    </div>
                    <div class="form__group-content">
                        <div class="form__input--text">
                            <textarea name="content">{{ old('content') }}</textarea>
                            <div class="form__error">
                                @error('content')
                                {{ $message }}
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form__button">
                    <button class="form__button-submit" type="submit">コメントを送信する</button>
                </div>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection


@section('script')
<script>
    document.addEventListener("DOMContentLoaded", () => {
        document.querySelectorAll(".favorite-btn").forEach(button => {
            button.addEventListener("click", async (e) => {
                e.preventDefault();

                const isAuth = button.dataset.auth === "true";
                const isOwner = button.dataset.owner === "true";

                if (!isAuth) {
                    window.location.href = "/login";
                    return;
                }

                if (isOwner) {
                    return;
                }

                const productId = button.dataset.id;
                const isFavorited = button.dataset.favorited === "true";

                const url = `/item/${productId}/favorite`;
                const method = isFavorited ? "DELETE" : "POST";

                const response = await fetch(url, {
                    method,
                    headers: {
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]').content,
                        "Accept": "application/json",
                    },
                });

                if (response.ok) {
                    const countSpan = button.nextElementSibling;
                    let currentCount = parseInt(countSpan.textContent, 10);

                    if (isFavorited) {
                        button.querySelector("img").src = "/images/icon-star.png";
                        button.dataset.favorited = "false";
                        countSpan.textContent = currentCount - 1;
                    } else {
                        button.querySelector("img").src = "/images/icon-star-filled.png";
                        button.dataset.favorited = "true";
                        countSpan.textContent = currentCount + 1;
                    }
                }
            });
        });
    });
</script>
@endsection