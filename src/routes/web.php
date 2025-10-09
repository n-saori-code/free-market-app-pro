<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\OrderController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

##会員登録画面
Route::post('/register', [AuthController::class, 'register']);

##ログイン画面
Route::post('/login', [AuthController::class, 'login']);

##商品一覧画面
Route::get('/', [ItemController::class, 'index'])->name('item.index');

##商品詳細画面
Route::get('/item/{item_id}', [ItemController::class, 'show'])->name('item.show');

##商品検索
Route::get('/search', [ItemController::class, 'search'])->name('item.search');

// メール認証待ちページ
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

// メール内リンククリック時（認証完了）
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill(); // 認証完了
    return redirect('/mypage/profile'); // 認証後のリダイレクト先
})->middleware(['auth', 'signed'])->name('verification.verify');

// 認証メール再送
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back();
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');


Route::middleware(['auth', 'verified'])->group(
    function () {
        ##マイページ
        Route::get('/mypage', [ProfileController::class, 'mypage'])->name('mypage');
        Route::get('/mypage/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/mypage/profile', [ProfileController::class, 'update'])->name('profile.update');

        ##商品出品画面
        Route::get('/sell', [ItemController::class, 'create'])->name('sell');
        Route::post('/sell', [ItemController::class, 'store']);

        ##商品購入画面
        Route::get('/purchase/{item_id}', [OrderController::class, 'showPurchaseForm'])->name('purchase.show');
        Route::post('/purchase/{item_id}', [OrderController::class, 'purchase'])->name('purchase.store');

        ##送付先住所変更画面
        Route::get('/purchase/address/{item_id}', [OrderController::class, 'showAddressForm'])->name('address.show');
        Route::post('/purchase/address/{item_id}', [OrderController::class, 'updateAddress'])->name('address.update');

        ## Stripe決済 成功・キャンセル
        Route::get('/purchase/success/{item_id}', [OrderController::class, 'success'])->name('purchase.success');
        Route::get('/purchase/cancel/{item_id}', [OrderController::class, 'cancel'])->name('purchase.cancel');

        ##いいねボタン
        Route::post('/item/{id}/favorite', [FavoriteController::class, 'favorite'])->name('favorite');
        Route::delete('/item/{id}/favorite', [FavoriteController::class, 'unfavorite'])->name('unfavorite');

        ##コメント
        Route::post('/products/{product}/comments', [CommentController::class, 'store'])->name('comments.store');
    }
);
