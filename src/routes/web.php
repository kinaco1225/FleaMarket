<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\EmailVerificationController;
use Illuminate\Support\Facades\Route;
use App\Models\Item;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| 画面遷移・ユーザー操作に関するルーティング定義
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| 購入完了・キャンセル（Stripe戻り先）
|--------------------------------------------------------------------------
*/

// 購入完了後（Stripe success URL）
Route::get('/purchase/success', [PurchaseController::class, 'success'])
    ->name('purchase.success');

// 購入キャンセル時（一時保存した住所を破棄）
Route::get('/purchase/cancel/{item}', function (Item $item) {

    // セッションに保存した一時住所を削除
    session()->forget("purchase_address_{$item->id}");

    return redirect()
        ->route('purchase.create', $item)
        ->with('info', '購入をキャンセルしました');
})->name('purchase.cancel');


/*
|--------------------------------------------------------------------------
| トップ・商品一覧・商品詳細
|--------------------------------------------------------------------------
*/

// トップページ（商品一覧）
Route::get('/', [ItemController::class, 'index'])
    ->name('items.index');

// 商品詳細ページ
Route::get('/item/{item}', [ItemController::class, 'show'])
    ->name('items.show');


/*
|--------------------------------------------------------------------------
| メール認証関連
|--------------------------------------------------------------------------
*/

// 認証メール送信完了画面
Route::get('/email/verify/send', [EmailVerificationController::class, 'sent'])
    ->name('verification.sent');


/*
|--------------------------------------------------------------------------
| ログイン必須ルート
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | メール認証（ログイン後）
    |--------------------------------------------------------------------------
    */

    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])
        ->name('verification.notice');

    Route::post('/email/verify/send', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1')
        ->name('verification.send.view');

    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');


    /*
    |--------------------------------------------------------------------------
    | マイページ・プロフィール
    |--------------------------------------------------------------------------
    */

    // マイページ（出品・購入タブ切替）
    Route::get('/mypage', [ProfileController::class, 'index'])
        ->name('mypage');

    // プロフィール編集画面
    Route::get('/mypage/profile', [ProfileController::class, 'edit'])
        ->name('mypage.profile');

    // プロフィール更新
    Route::post('/mypage/profile', [ProfileController::class, 'update'])
        ->name('profile.update');


    /*
    |--------------------------------------------------------------------------
    | 商品出品
    |--------------------------------------------------------------------------
    */

    // 出品画面
    Route::get('/sell', [ItemController::class, 'create'])
        ->name('items.create');

    // 出品保存
    Route::post('/items', [ItemController::class, 'store'])
        ->name('items.store');


    /*
    |--------------------------------------------------------------------------
    | 購入フロー
    |--------------------------------------------------------------------------
    */

    // 商品購入画面
    Route::get('/purchase/{item}', [PurchaseController::class, 'create'])
        ->name('purchase.create');

    // 配送先住所変更画面
    Route::get('/purchase/{item}/address', [PurchaseController::class, 'editAddress'])
        ->name('purchase.address');

    // 配送先住所の一時保存（session）
    Route::post('/purchase/{item}/address', [PurchaseController::class, 'storeAddress'])
        ->name('purchase.address.store');

    // Stripe Checkout 作成
    Route::post('/purchase/checkout', [PurchaseController::class, 'checkout'])
        ->name('purchase.checkout');


    /*
    |--------------------------------------------------------------------------
    | コメント・いいね機能
    |--------------------------------------------------------------------------
    */

    // コメント投稿
    Route::post('/items/{item}/comments', [CommentController::class, 'store'])
        ->name('comments.store');

    // いいね追加
    Route::post('/items/{item}/like', [LikeController::class, 'store'])
        ->name('likes.store');

    // いいね解除
    Route::delete('/items/{item}/like', [LikeController::class, 'destroy'])
        ->name('likes.destroy');
});

