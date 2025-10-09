{{-- resources/views/components/header-link.blade.php --}}

<div class="header__content">
    <form class="header-niv__form" action="{{ route('item.search') }}" method="get">
        @csrf
        <input class="search-form__keyword-input" type="text" name="keyword" placeholder="なにをお探しですか？" value="{{ $keyword ?? '' }}">
    </form>

    <ul class="header-nav">
        @guest
        <li class="header-nav__item">
            <a class="header-nav__login-link" href="/login">ログイン</a>
        </li>
        @endguest

        @if (Auth::check())
        <li class="header-nav__item">
            <form action="/logout" method="post" method="post">
                @csrf
                <button class="header-nav__button">ログアウト</button>
            </form>
        </li>
        @endif

        <li class="header-nav__item">
            <a class="header-nav__mypage-link" href="/mypage">マイページ</a>
        </li>

        <li class="header-nav__item">
            <button class="header-nav__sell-link"><a href="/sell">出品</a></button>
        </li>
    </ul>
</div>