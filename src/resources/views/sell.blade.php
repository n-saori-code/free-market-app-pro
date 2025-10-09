@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/sell.css')}}">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
@endsection

@section('link')
@include('components.header-link')
@endsection

@section('content')
<div class="sell-form__content">
    <div class="sell-form__heading">
        <h2>商品の出品</h2>
    </div>

    <form class="form" action="{{ route('sell') }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf
        <div class="form__group-title">
            <span class="form__label--item">商品画像</span>
        </div>
        <div class="upload-box">
            <label for="image-upload" class="upload-label">画像を選択する</label>
            <input type="file" id="image-upload" name="profile_image" accept="image/*" hidden>
            <div class="file-name" id="file-name"></div>
        </div>
        <div class="form__error">
            @error('profile_image')
            {{ $message }}
            @enderror
        </div>

        <h3 class="form__item__title">商品の詳細</h3>
        <div class="form__group">
            <div class="form__group-title">
                <span class="form__label--item">カテゴリー</span>
            </div>
            <div class="form__error">
                @error('category_id')
                {{ $message }}
                @enderror
            </div>
            <div class="form__group-content">
                <div class="form__input--select category-select">
                    @foreach($categories as $category)
                    <input type="checkbox" id="category-{{ $category->id }}" name="category_id[]" value="{{ $category->id }}" hidden>
                    <label for="category-{{ $category->id }}">{{ $category->category_name }}</label>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="form__group">
            <div class="form__group-title">
                <span class="form__label--item">商品の状態</span>
            </div>
            <div class="form__group-content">
                <div class="form__input--select">
                    <select class="condition" name="condition" required>
                        <option value="" disabled selected hidden>選択してください</option>
                        @foreach($conditions as $condition)
                        <option value="{{ $condition->id }}">{{ $condition->condition_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form__error">
                    @error('condition')
                    {{ $message }}
                    @enderror
                </div>
            </div>
        </div>

        <h3 class="form__item__title">商品名と説明</h3>
        <div class="form__group">
            <div class="form__group-title">
                <span class="form__label--item">商品名</span>
            </div>
            <div class="form__group-content">
                <div class="form__input--text">
                    <input type="text" name="title" value="{{ old('title') }}" />
                </div>
                <div class="form__error">
                    @error('title')
                    {{ $message }}
                    @enderror
                </div>
            </div>
        </div>

        <div class="form__group">
            <div class="form__group-title">
                <span class="form__label--item">ブランド名</span>
            </div>
            <div class="form__group-content">
                <div class="form__input--text">
                    <input type="text" name="brand" value="{{ old('brand') }}" />
                </div>
                <div class="form__error">
                    @error('brand')
                    {{ $message }}
                    @enderror
                </div>
            </div>
        </div>

        <div class="form__group">
            <div class="form__group-title">
                <span class="form__label--item">商品の説明</span>
            </div>
            <div class="form__group-content">
                <div class="form__input--description">
                    <textarea name="description">{{ old('description') }}</textarea>
                </div>
                <div class="form__error">
                    @error('description')
                    {{ $message }}
                    @enderror
                </div>
            </div>
        </div>

        <div class="form__group">
            <div class="form__group-title">
                <span class="form__label--item">販売価格</span>
            </div>
            <div class="form__group-content">
                <div class="form__input--text price">
                    <input type="text" name="price" value="{{ old('price') }}" />
                    <span class="currency">¥</span>
                </div>
                <div class="form__error">
                    @error('price')
                    {{ $message }}
                    @enderror
                </div>
            </div>
        </div>

        <div class="form__button">
            <button class="form__button-submit" type="submit">出品する</button>
        </div>
    </form>
</div>
@endsection


@section('script')
<script>
    const imageInput = document.getElementById('image-upload');
    const fileNameDisplay = document.getElementById('file-name');

    imageInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            fileNameDisplay.textContent = file.name;
        } else {
            fileNameDisplay.textContent = '';
        }
    });
</script>

@endsection