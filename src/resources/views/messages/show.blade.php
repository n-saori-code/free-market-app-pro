@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/messages/show.css')}}">
@endsection

@section('content')
<div class="transaction-container">

    <!-- 左サイドバー -->
    <aside class="transaction-sidebar">
        <h3 class="sidebar-title">その他の取引</h3>
        <ul class="transaction-list">
            @foreach($tradingOrders as $tradingOrder)
            <li class="transaction-item
                {{ $tradingOrder->id === $order->id ? 'active' : '' }}">
                <a href="{{ route('messages.show', $tradingOrder->id) }}">
                    {{ $tradingOrder->product->title }}
                </a>
            </li>
            @endforeach
        </ul>
    </aside>

    <!-- チャット取引画面 -->
    <main class="transaction-main">

        <!-- ヘッダー -->
        <div class="transaction-header">
            <div class="header-left">
                @if($partner->profile && $partner->profile->profile_image)
                <img
                    src="{{ asset('storage/' . $partner->profile->profile_image) }}"
                    class="avatar large"
                    alt="{{ $partner->name }}">
                @else
                <div class="avatar large"></div>
                @endif

                <h2>{{ $partner->name }}さんとの取引画面</h2>
            </div>

            @if(
            $order->status === \App\Models\Order::STATUS_IN_CHAT &&
            $order->buyer_id === auth()->id()
            )
            <form method="POST" action="{{ route('orders.complete', $order->id) }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="complete-button">
                    取引を完了する
                </button>
            </form>
            @endif
        </div>

        <div class="transaction-body">
            <!-- 商品情報 -->
            <div class="product-info">
                <img class="product-image"
                    src="{{ asset('storage/' . $order->product->image) }}"
                    alt="{{ $order->product->title }}">

                <div class="product-text">
                    <h3>{{ $order->product->title }}</h3>
                    <p>¥{{ number_format($order->product->price) }}</p>
                </div>
            </div>

            <!-- チャット -->
            <div class="chat-area">

                @foreach($messages as $message)
                @php
                $isMine = $message->sender_id === $user->id;
                $sender = $message->sender;
                @endphp

                <div class="message-row {{ $isMine ? 'right' : 'left' }}">
                    <div class="message-box">

                        <div class="message-header {{ $isMine ? 'reverse' : '' }}">
                            @unless($isMine)
                            @include('messages.parts.avatar', ['user' => $sender])
                            @endunless

                            <p class="user-name">{{ $sender->name }}</p>

                            @if($isMine)
                            @include('messages.parts.avatar', ['user' => $sender])
                            @endif
                        </div>

                        <div class="message-content">
                            {{ $message->content }}

                            @if($message->image)
                            <img src="{{ asset('storage/' . $message->image) }}" class="message-image">
                            @endif
                        </div>

                        @if($isMine)
                        <div class="message-actions">
                            <!-- 編集 -->
                            <button
                                type="button"
                                class="edit-button"
                                data-id="{{ $message->id }}"
                                data-content="{{ e($message->content) }}">
                                編集
                            </button>

                            <!-- 削除 -->
                            <form
                                method="POST"
                                action="{{ route('messages.destroy', [$order->id, $message->id]) }}"
                                style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('コメントを削除しますか？')">
                                    削除
                                </button>
                            </form>
                        </div>
                        @endif

                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- 入力欄 -->
        @if ($errors->any())
        <div class="form__error">
            @foreach ($errors->all() as $error)
            <p class="error-message">{{ $error }}</p>
            @endforeach
        </div>
        @endif

        <form class="message-form"
            method="POST"
            action="{{ route('messages.store', $order->id) }}"
            enctype="multipart/form-data">
            @csrf

            <textarea id="messageContent" name="content" placeholder="取引メッセージを記入してください" rows="3">{{ old('content') }}</textarea>

            <div class="selected-image" id="selectedImageName" style="display: none;">
                <span class="file-name"></span>
            </div>

            <label for="image" class="image-button">画像を追加</label>
            <input id="image" type="file" name="image" accept=".png,.jpeg" hidden>

            <button type="submit" class="send-button">
                <img src="{{ asset('images/send.jpg') }}" alt="送信">
            </button>
        </form>
    </main>

    @if($shouldShowReviewModal)
    <div class="review-modal-overlay">
        <div class="review-modal">

            <h3 class="review-title">取引が完了しました。</h3>
            <p class="review-text">今回の取引相手はどうでしたか？</p>

            <form method="POST" action="{{ route('reviews.store', $order) }}">
                @csrf

                <div class="rating">
                    @for($i = 5; $i >= 1; $i--)
                    <input
                        type="radio"
                        id="star{{ $i }}"
                        name="rating"
                        value="{{ $i }}"
                        required>
                    <label for="star{{ $i }}">★</label>
                    @endfor
                </div>

                <div class="review-actions">
                    <button type="submit" class="review-submit">
                        送信する
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>

@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', () => {

        // 画像選択表示
        const imageInput = document.getElementById('image');
        if (imageInput) {
            imageInput.addEventListener('change', function() {
                const file = this.files[0];
                const container = document.getElementById('selectedImageName');
                const fileNameSpan = container.querySelector('.file-name');

                if (file) {
                    fileNameSpan.textContent = file.name;
                    container.style.display = 'block';
                } else {
                    container.style.display = 'none';
                    fileNameSpan.textContent = '';
                }
            });
        }

        // 本文下書き保存
        const textarea = document.getElementById('messageContent');
        if (!textarea) return;

        const storageKey = 'chat_draft_order_{{ $order->id }}';

        const saved = localStorage.getItem(storageKey);
        if (saved && !textarea.value) {
            textarea.value = saved;
        }

        textarea.addEventListener('input', () => {
            localStorage.setItem(storageKey, textarea.value);
        });

        textarea.closest('form').addEventListener('submit', () => {
            localStorage.removeItem(storageKey);
        });

        // コメント編集
        const editButtons = document.querySelectorAll('.edit-button');
        const form = document.querySelector('.message-form');

        editButtons.forEach(button => {
            button.addEventListener('click', () => {
                const messageId = button.dataset.id;
                const content = button.dataset.content;

                textarea.value = content;
                textarea.focus();

                form.action = `/orders/{{ $order->id }}/messages/${messageId}`;

                if (!form.querySelector('input[name="_method"]')) {
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'PUT';
                    form.appendChild(methodInput);
                }
            });
        });
    });
</script>
@endsection