@extends('layouts.header')

@section('title', '住所変更')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/address_change.css') }}?v={{ time() }}">
@endsection

@section('content')
<div class="address-change-container">

  <h1 class="address-change-title">住所変更</h1>

  <form action="{{ route('purchase.address.store', $item) }}" method="POST" >
  @csrf
    <input type="hidden" name="item_id" value="{{ $item->id }}">

    <div class="form-group">
      <label>郵便番号</label>
      <input type="text" name="postal_code" value="{{ old('postal_code') }}">
      @error('postal_code')<p class="error-text">{{ $message }}</p>@enderror
    </div>

    <div class="form-group">
      <label>住所</label>
      <input type="text" name="address" value="{{ old('address') }}">
      @error('address')<p class="error-text">{{ $message }}</p>@enderror
    </div>

    <div class="form-group">
      <label>建物名</label>
      <input type="text" name="building" value="{{ old('building') }}">
    </div>

    <button type="submit" class="btn-primary">更新する</button>

  </form>

</div>

@endsection