@extends('layouts.header')

@section('title', '商品購入')

@section('css')
<link rel="stylesheet" href="{{ asset('css/purchase.css') }}?v={{ time() }}">
@endsection

@section('content')

<form action="{{ route('purchase.checkout') }}" method="POST" class="purchase-container">
  @csrf

  <input type="hidden" name="item_id" value="{{ $item->id }}">
  @if ($address instanceof \App\Models\Address)
    <input type="hidden" name="address_id" value="{{ $address->id }}">
  @endif

  {{-- 左側 --}}
  <div class="purchase-left">

    <div class="purchase-item">
      <img src="{{ asset('storage/' . $item->image_path) }}" class="purchase-image">
      <div class="purchase-item-info">
        <h1>{{ $item->name }}</h1>
        <p class="price"><span>¥</span>{{ number_format($item->price) }}</p>
      </div>
    </div>

    {{-- 支払い方法 --}}
    <div class="purchase-block">
      <label class="purchase-label">支払い方法</label>
      <select name="payment_method" class="purchase-select" id="paymentMethodSelect">
        <option value="" disabled {{ old('payment_method') ? '' : 'selected' }} hidden>
          選択してください
        </option>

        <option value="konbini" {{ old('payment_method') === 'konbini' ? 'selected' : '' }}>
          コンビニ払い
        </option>

        <option value="card" {{ old('payment_method') === 'card' ? 'selected' : '' }}>
          カード支払い
        </option>
      </select>
      @error('payment_method')
        <p class="purchase-error">{{ $message }}</p>
      @enderror
    </div>

    {{-- 配送先 --}}
    <div class="purchase-block">
      <div class="purchase-address-header">
        <span>配送先</span>
        <a href="{{ route('purchase.address', $item) }}" class="change-link">変更する</a>
      </div>

      <div class="purchase-address">
        @if ($address)
          <p class="purchase-postal">〒 {{ $address->postal_code }}</p>
          <p class="purchase-full">{{ $address->address }}</p>
          <p class="purchase-building">{{ $address->building }}</p>
        @else
          <p class="purchase-empty">配送先が設定されていません</p>
          
        @endif

        @error('address')
          <p class="purchase-error">{{ $message }}</p>
        @enderror
      </div>

    </div>
  </div>

  {{-- 右側 --}}
  <div class="purchase-right">
    <table class="purchase-summary-table">
      <tr>
        <th>商品代金</th>
        <td><span>¥</span>{{ number_format($item->price) }}</td>
      </tr>
      <tr>
        <th>支払い方法</th>
        <td class="summary-method" id="summaryMethod">未選択</td>
      </tr>
    </table>

    <button type="submit" class="purchase-button">
      購入する
    </button>

  </div>

</form>

@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const select = document.getElementById('paymentMethodSelect');
  const summary = document.getElementById('summaryMethod');

  if (!select || !summary) return;

  select.addEventListener('change', function () {
    summary.textContent = this.value === 'konbini'
      ? 'コンビニ払い'
      : this.value === 'card'
      ? 'カード支払い'
      : '未選択';
  });
});
</script>
@endsection