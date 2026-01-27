@extends('layouts.header')

@section('title', 'マイページ')

@section('css')
<link rel="stylesheet" href="{{ asset('css/profile.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/top.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/items.css') }}?v={{ time() }}">
@endsection

@section('content')
<div class="mypage-image-area">

  <div class="mypage-left">
    <div class="profile-image-preview">
      @if ($user->profile_image)
        <img id="profilePreview" src="{{ asset('storage/' . $user->profile_image) }}" alt="プロフィール画像">
      @else
        <div class="profile-image-placeholder" id="profilePlaceholder"></div>
      @endif
    </div>
  </div>

  <div class="mypage-center">
      <p class="user-name">{{ $user->name }}</p>
  </div>

  <div class="mypage-right">
    <a class="edit-profile-link" href="{{ route('mypage.profile') }}">プロフィールを編集</a>
  </div>

</div>

<div class="items-tabs">
  <a href="{{ route('mypage', ['page' => 'sell']) }}"
     class="items-tab {{ request('page', 'sell') === 'sell' ? 'active' : '' }}">
     出品した商品
  </a>

  <a href="{{ route('mypage', ['page' => 'buy']) }}"
     class="items-tab {{ request('page') === 'buy' ? 'active' : '' }}">
     購入した商品
  </a>
</div>

<div class="items-container">
  <div class="items-grid">
    @forelse ($items as $item)
      <div class="mypage-item-card">
        <div class="item-image">
          <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}">
        </div>
        <p class="item-name">{{$item->name}}</p>
      </div>
    @empty
      <p>
        {{ $page === 'buy' ? 'まだ購入していません。' : 'まだ出品していません。' }}
      </p>
    @endforelse
  </div>
</div>
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const input = document.getElementById('profileImageInput');
  if (!input) return;

  input.addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function (event) {
      const img = document.getElementById('profilePreview');
      const placeholder = document.getElementById('profilePlaceholder');

      img.src = event.target.result;
      img.style.display = 'block';
      placeholder.style.display = 'none';
    };
    reader.readAsDataURL(file);
  });
});
</script>
@endsection