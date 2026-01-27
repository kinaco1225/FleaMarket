<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Address;
use App\Models\Purchase;
use App\Http\Requests\AddressRequest;
use App\Http\Requests\PurchaseRequest;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Illuminate\Support\Facades\Auth;


class PurchaseController extends Controller
{
    /**
     * 購入画面表示
     */
    public function create(Item $item)
    {
        if ($item->is_sold) {
            return redirect()
                ->route('items.index')
                ->with('error', 'この商品はすでに購入されています');
        }

        $user = Auth::user();

        // ① セッション住所（最優先）
        if (session()->has("purchase_address_{$item->id}")) {
            $address = (object) session("purchase_address_{$item->id}");
        }
        // ② 商品に紐づく住所（過去に変更・保存されたもの）
        elseif (
            $itemAddress = Address::where('user_id', $user->id)
            ->where('item_id', $item->id)
            ->first()
        ) {
            $address = $itemAddress;
        }
        // ③ ユーザーの初期住所
        else {
            $address = (object) [
                'postal_code' => $user->postal_code,
                'address'     => $user->address,
                'building'    => $user->building,
            ];
        }

        if (
            empty($address->postal_code) ||
            empty($address->address)
        ) {
            $address = null;
        }

        return view('purchases.purchase', compact('item', 'address'));
    }

    /**
     * 住所変更画面
     */
    public function editAddress(Item $item)
    {
        return view('purchases.address_change', compact('item'));
    }

    /**
     * 住所変更（※ DB保存しない）
     */
    public function storeAddress(AddressRequest $request, Item $item)
    {
        session([
            "purchase_address_{$item->id}" => [
                'postal_code' => $request->postal_code,
                'address'     => $request->address,
                'building'    => $request->building,
            ]
        ]);

        return redirect()
            ->route('purchase.create', $item)
            ->with('success', '配送先を変更しました');
    }

    /**
     * 決済処理
     */
    public function checkout(PurchaseRequest $request)
    {
        $item = Item::findOrFail($request->item_id);

        Stripe::setApiKey(config('services.stripe.secret'));

        $paymentMethod = $request->payment_method;

        if (!in_array($paymentMethod, ['card', 'konbini'])) {
            abort(400, '不正な支払い方法です');
        }

        $session = StripeSession::create([
            'payment_method_types' => [$paymentMethod],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'jpy',
                    'product_data' => ['name' => $item->name],
                    'unit_amount' => $item->price,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'metadata' => [
                'user_id'        => Auth::id(),
                'item_id'        => $item->id,
                'payment_method' => $paymentMethod,
            ],
            'success_url' => route('purchase.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('purchase.cancel', ['item' => $item->id]),
        ]);

        return redirect($session->url);
    }

    /**
     * 購入完了
     */
    public function success(Request $request)
    {
        // テスト環境では Stripe をスキップ
        if (app()->environment('testing')) {
            $itemId = session('purchased_item_id');
            $item = Item::findOrFail($itemId);

            Purchase::create([
                'user_id' => auth()->id(),
                'item_id' => $item->id,
            ]);

            $item->update(['is_sold' => true]);

            return redirect()->route('items.index');
        }
        
        // ===== 本番処理 =====
        Stripe::setApiKey(config('services.stripe.secret'));

        $sessionId = $request->get('session_id');
        if (!$sessionId) abort(400, 'session_id が存在しません');

        $stripeSession = StripeSession::retrieve($sessionId);
        if (!$stripeSession) abort(400, 'Stripe セッションが取得できません');

        $metadata = $stripeSession->metadata ?? null;
        if (!$metadata) abort(400, 'metadata が存在しません');

        $itemId  = $metadata->item_id ?? null;
        $payment = $metadata->payment_method ?? 'card';

        if (!$itemId) abort(400, 'item_id が metadata に存在しません');

        $userId = Auth::id();
        if (!$userId) abort(403, '未ログインです');

        /**
         * 配送先住所を決定
         * ① セッション住所
         * ② users / 初期住所
         */
        $addressData = session("purchase_address_{$itemId}");

        if (! $addressData) {
            $user = Auth::user();

            if (! $user || empty($user->postal_code) || empty($user->address)) {
                abort(400, '配送先住所が設定されていません');
            }

            $addressData = [
                'postal_code' => $user->postal_code,
                'address'     => $user->address,
                'building'    => $user->building,
            ];
        }

        // 商品専用の住所として保存
        $address = Address::updateOrCreate(
            [
                'user_id' => $userId,
                'item_id' => $itemId,
            ],
            [
                'postal_code' => $addressData['postal_code'],
                'address'     => $addressData['address'],
                'building'    => $addressData['building'],
            ]
        );

        // 購入情報保存
        Purchase::create([
            'user_id'        => $userId,
            'item_id'        => $itemId,
            'payment_method' => $payment,
            'address_id'     => $address->id,
        ]);

        // 商品を売却済みに
        $item = Item::findOrFail($itemId);
        $item->update(['is_sold' => true]);

        // セッション削除
        session()->forget("purchase_address_{$itemId}");

        return redirect()
            ->route('items.index')
            ->with('success', '購入が完了しました');
    }
}
