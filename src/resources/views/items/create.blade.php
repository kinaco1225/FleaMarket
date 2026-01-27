@extends('layouts.header')

@section('title', '商品出品画面')

@section('css')
<link rel="stylesheet" href="{{ asset('css/sell.css') }}?v={{ time() }}">
@endsection

@section('content')
<div class="sell-page">
  <div class="sell-container">
    <h1 class="sell-title">商品の出品</h1>

    <form action="{{ route('items.store') }}" method="POST" enctype="multipart/form-data" class="sell-form">
      @csrf

      {{-- 画像 --}}
      <div class="sell-section">
        <h2 class="sell-label">商品画像</h2>

        <div class="sell-image-box">
          <img id="imagePreview" class="sell-image-preview" src="" alt="" style="display:none;">
          <label class="sell-image-button">
            画像を選択する
            <input id="imageInput" type="file" name="image" accept="image/*" hidden>
          </label>
        </div>

        @error('image')
          <p class="sell-error">{{ $message }}</p>
        @enderror
      </div>

      {{-- 詳細 --}}
      <div class="sell-section">
        <h2 class="sell-section-title">商品の詳細</h2>

        {{-- カテゴリー --}}
        <div class="sell-field">
          <label class="sell-label">カテゴリー</label>

          <div class="sell-category">

            @foreach($categories as $category)
              <button
                type="button"
                class="cat-pill {{ in_array($category->id, old('category_ids', [])) ? 'is-active' : '' }}"
                data-id="{{ $category->id }}">
                {{ $category->name }}
              </button>
            @endforeach
            <div id="categoryHiddenArea"></div>
          </div>

          @error('category_ids')
            <p class="sell-error">{{ $message }}</p>
          @enderror
        </div>

        {{-- 商品の状態 --}}
        <div class="sell-field">
          <label class="sell-label">商品の状態</label>

          <select name="status" class="sell-select">
            <option value=""hidden disabled {{ old('status') === null ? 'selected' : '' }}>選択してください</option>
            @foreach(\App\Models\Item::statusLabels() as $key => $label)
              <option value="{{ $key }}" {{ old('status') == $key ? 'selected' : '' }}>
                {{ $label }}
              </option>
            @endforeach
          </select>

          @error('status')
            <p class="sell-error">{{ $message }}</p>
          @enderror

        </div>

      </div>

      {{-- 商品名と説明 --}}
      <div class="sell-section">
        <h2 class="sell-section-title">商品名と説明</h2>

        <div class="sell-field">
          <label class="sell-label" for="name">商品名</label>
          <input type="text" id="name" name="name" class="sell-input" value="{{ old('name') }}">
          @error('name')
            <p class="sell-error">{{ $message }}</p>
          @enderror
        </div>

        <div class="sell-field">
          <label class="sell-label" for="brand">ブランド名</label>
          <input type="text" id="brand" name="brand" class="sell-input" value="{{ old('brand') }}">
          @error('brand')
            <p class="sell-error">{{ $message }}</p>
          @enderror
        </div>

        <div class="sell-field">
          <label class="sell-label" for="description">商品の説明</label>
          <textarea id="description" name="description" class="sell-textarea">{{ old('description') }}</textarea>
          @error('description')
            <p class="sell-error">{{ $message }}</p>
          @enderror
        </div>
      </div>

      {{-- 価格 --}}
      <div class="sell-section">
        <h2 class="sell-section-title">販売価格</h2>

        <div class="sell-field sell-price">
          <span class="sell-yen">¥</span>
          <input type="number" name="price" class="sell-input sell-price-input" value="{{ old('price') }}" min="0">
        </div>

        @error('price')
          <p class="sell-error">{{ $message }}</p>
        @enderror
      </div>

      <button type="submit" class="sell-submit">出品する</button>
    </form>
  </div>
</div>
@endsection

@section('js')
<script>
  // 画像プレビュー
  document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('imageInput');
    const preview = document.getElementById('imagePreview');

    if (input) {
      input.addEventListener('change', (e) => {
        const file = e.target.files?.[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = (ev) => {
          preview.src = ev.target.result;
          preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
      });
    }
  });
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const pills = document.querySelectorAll('.cat-pill');
  const hiddenArea = document.getElementById('categoryHiddenArea');

  function syncHidden() {
    hiddenArea.innerHTML = '';
    document.querySelectorAll('.cat-pill.is-active').forEach(p => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'category_ids[]';
      input.value = p.dataset.id;
      hiddenArea.appendChild(input);
    });
  }

  pills.forEach(p => {
    p.addEventListener('click', () => {
      p.classList.toggle('is-active');
      syncHidden();
    });
  });

  syncHidden();
});
</script>

@endsection