@extends('layouts.header')

@section('title', 'プロフィール設定')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/profile.css') }}?v={{ time() }}">
@endsection

@section('content')
<div class="profile-container">

  <h1 class="profile-title">プロフィール設定</h1>

  <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="profile-form">
    @csrf

      <div class="profile-image-area">
        <div class="profile-image-preview">
        @if ($user->profile_image)
          <img id="profilePreview" src="{{ asset('storage/' . $user->profile_image) }}" alt="プロフィール画像">
        @else
          <div class="profile-image-placeholder" id="profilePlaceholder"></div>
        @endif
      </div>

      <label class="profile-image-button">
        画像を選択する
        <input type="file" name="profile_image" id="profileImageInput" hidden>
      </label>

      @error('profile_image')
        <p class="error-text">{{ $message }}</p>
      @enderror
    </div>

    <div class="form-group">
      <label>ユーザー名</label>
      <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}">
      @error('name')<p class="error-text">{{ $message }}</p>@enderror
    </div>

    <div class="form-group">
      <label>郵便番号</label>
      <input type="text" name="postal_code" value="{{ old('postal_code', auth()->user()->postal_code) }}" placeholder="">
      @error('postal_code')<p class="error-text">{{ $message }}</p>@enderror
    </div>

    <div class="form-group">
      <label>住所</label>
      <input type="text" name="address" value="{{ old('address', auth()->user()->address) }}">
      @error('address')<p class="error-text">{{ $message }}</p>@enderror
    </div>

    <div class="form-group">
      <label>建物名</label>
      <input type="text" name="building" value="{{ old('building', auth()->user()->building) }}">
    </div>

    <button type="submit" class="btn-primary">更新する</button>

  </form>
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
      let img = document.getElementById('profilePreview');
      let placeholder = document.getElementById('profilePlaceholder');

      // 画像が無かった場合は生成する
      if (!img) {
        img = document.createElement('img');
        img.id = 'profilePreview';
        img.alt = 'プロフィール画像';
        document.querySelector('.profile-image-preview').appendChild(img);
      }

      img.src = event.target.result;
      img.style.display = 'block';

      if (placeholder) {
        placeholder.style.display = 'none';
      }
    };

    reader.readAsDataURL(file);
  });
});
</script>
@endsection