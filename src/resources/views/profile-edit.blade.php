@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/profile-edit.css')}}">
@endsection

@section('link')
@include('components.header-link')
@endsection

@section('content')
<div class="profile-form__content">
    <div class="profile-form__heading">
        <h2>プロフィール設定</h2>
    </div>

    <form class="form" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf
        @method('PUT')
        <div class="form__group image">
            <div class="form__group-image">
                @if($user->profile && $user->profile->profile_image)
                <img src="{{ asset('storage/'.$user->profile->profile_image) }}" alt="ユーザー写真" class="user__image">
                @else
                <div class="user__image-circle"></div>
                @endif
            </div>
            <label for="image-upload" class="upload-label">画像を選択する</label>
            <input type="file" id="image-upload" name="profile_image" accept="image/*" hidden>
            <div class="form__error">
                @error('profile_image')
                {{ $message }}
                @enderror
            </div>
        </div>

        <div class="form__group">
            <div class="form__group-title">
                <span class="form__label--item">ユーザー名</span>
            </div>
            <div class="form__group-content">
                <div class="form__input--text">
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" />
                </div>
                <div class="form__error">
                    @error('name')
                    {{ $message }}
                    @enderror
                </div>
            </div>
        </div>

        <div class="form__group">
            <div class="form__group-title">
                <span class="form__label--item">郵便番号</span>
            </div>
            <div class="form__group-content">
                <div class="form__input--text">
                    <input type="text" name="postal_code" value="{{ old('postal_code', $user->profile->postal_code ?? '') }}" />
                </div>
                <div class="form__error">
                    @error('postal_code')
                    {{ $message }}
                    @enderror
                </div>
            </div>
        </div>

        <div class="form__group">
            <div class="form__group-title">
                <span class="form__label--item">住所</span>
            </div>
            <div class="form__group-content">
                <div class="form__input--text">
                    <input type="text" name="address" value="{{ old('address', $user->profile->address ?? '') }}" />
                </div>
                <div class="form__error">
                    @error('address')
                    {{ $message }}
                    @enderror
                </div>
            </div>
        </div>

        <div class="form__group">
            <div class="form__group-title">
                <span class="form__label--item">建物名</span>
            </div>
            <div class="form__group-content">
                <div class="form__input--text">
                    <input type="text" name="building" value="{{ old('building', $user->profile->building ?? '') }}" />
                </div>
            </div>
        </div>

        <div class="form__button">
            <button class="form__button-submit" type="submit">更新する</button>
        </div>

    </form>
</div>
@endsection


@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const imageInput = document.getElementById('image-upload');
        const imagePreviewContainer = document.querySelector('.form__group-image');

        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(event) {
                imagePreviewContainer.innerHTML = '';
                const img = document.createElement('img');
                img.src = event.target.result;
                img.alt = '選択された画像';
                img.classList.add('user__image');
                imagePreviewContainer.appendChild(img);
            }
            reader.readAsDataURL(file);
        });
    });
</script>

@endsection