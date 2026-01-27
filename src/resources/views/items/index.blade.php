@extends('layouts.header')

@section('title', 'トップページ')

@section('css')
<link rel="stylesheet" href="{{ asset('css/top.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/items.css') }}?v={{ time() }}">
@endsection

@section('content')

<div class="items-tabs">
  <a href="/?tab=recommend&keyword={{ request('keyword') }}"
     class="items-tab {{ request('tab') !== 'mylist' ? 'active' : '' }}">
    おすすめ
  </a>

  <a href="/?tab=mylist&keyword={{ request('keyword') }}"
     class="items-tab {{ request('tab') === 'mylist' ? 'active' : '' }}">
    マイリスト
  </a>
</div>

<div class="items-container">
  <div class="items-grid">

    @if (request('tab') === 'mylist' && !Auth::check())
      <p class="items-empty">マイリストを見るにはログインしてください。</p>

    @else
      @forelse ($items as $item)
        <div class="item-card">
          <div class="item-image">
            <a href="{{ route('items.show',$item->id) }}">
              <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}">
              @if ($item->is_sold)
                <div class="sold-label">SOLD</div>
              @endif
            </a>
          </div>
          <p class="item-name">{{ $item->name }}</p>
        </div>
      @empty
        <p class="items-empty">商品がありません。</p>
      @endforelse
    @endif

  </div>
</div>

@endsection