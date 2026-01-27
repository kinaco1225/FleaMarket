@extends('layouts.header')

@section('title', '商品詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/top.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/show.css') }}?v={{ time() }}">
@endsection

@section('content')

<div class="item-show">

  {{-- 左：画像 --}}
  <div class="item-show-left">
    <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}">
    @if ($item->is_sold)
      <div class="sold-label">SOLD</div>
    @endif
  </div>

  {{-- 右：情報 --}}
  <div class="item-show-right">
    <h1 class="item-name">{{ $item->name }}</h1>
    <p class="item-brand">{{ $item->brand ?? '' }}</p>

    <p class="item-price"><span>¥</span>{{ number_format($item->price) }} <span>(税込)</span></p>

    <div class="item-icons">
      @auth
      <button class="icon-block js-like-toggle"
        data-liked="{{ $item->likedUsers->contains(auth()->id()) ? 1 : 0 }}"
        data-item="{{ $item->id }}">

        <img class="like-img"
            src="{{ asset(
              $item->likedUsers->contains(auth()->id())
                ? 'images/ハートロゴ_ピンク.png'
                : 'images/ハートロゴ_デフォルト.png'
            ) }}">

        <span class="like-count">{{ $item->likedUsers->count() }}</span>
      </button>
      @endauth

      @guest
      <form action="{{ route('login') }}" method="GET">
        <button class="icon-block">
          <img src="{{ asset('images/ハートロゴ_デフォルト.png') }}">
          <span>{{ $item->likes->count() }}</span>
        </button>
      </form>
      @endguest

      <div class="icon-block">
        <img src="{{ asset('images/ふきだしロゴ.png') }}" alt="コメント">
        <span class="icon-count" id="comment-icon-count">
          {{ $item->comments->count() }}
        </span>
      </div>
    </div>


    @auth
      <a href="{{ route('purchase.create', $item) }}" class="buy-button">購入手続きへ</a>
    @else
      <a href="{{ route('login') }}" class="buy-button">購入手続きへ</a>
    @endauth

    <h2>商品説明</h2>
    <div class="item-description">
      {!! nl2br(e($item->description)) !!}
    </div>

    <h2>商品の情報</h2>
    <div class="item-info">

      <div class="item-info-row">
        <div class="item-info-label">カテゴリー</div>
        <div class="item-info-value">
          @forelse($item->categories as $category)
            <span class="item-category">{{ $category->name }}</span>
          @empty
            <span class="item-category">-</span>
          @endforelse
        </div>
      </div>

      <div class="item-info-row">
        <div class="item-info-label">商品の状態</div>
        <div class="item-info-value">
          <span class="item-status">{{ $item->status_label }}</span>
        </div>
      </div>

    </div>

    <h2>コメント (<span id="comment-count">{{ $item->comments->count() }}</span>)</h2>

    <div id="comment-list">
      <div id="comment-list">
        @include('items.partials.comments', ['comments' => $item->comments])
      </div>
    </div>

    <h3>商品へのコメント</h3>

    @auth
    <form id="comment-form" data-item="{{ $item->id }}">
      @csrf
      <textarea name="comment" id="comment-input" class="comment-textarea"></textarea>
      <p id="comment-error" class="comment-error" style="display:none;"></p>
      
      <button type="submit" class="comment-button">コメントを送信する</button>
    </form>
    @endauth
      
    @guest
      <form id="comment-form" action="{{ route('login') }}" method="GET">
        <textarea class="comment-textarea" placeholder="ログインするとコメントできます" readonly></textarea>
        <p class="comment-error" style="display:none;"></p>
        <button type="submit" class="comment-button">
          ログインしてコメントする
        </button>
      </form>
    @endguest
    
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


@auth
<script>
document.querySelectorAll('.js-like-toggle').forEach(btn => {
  btn.addEventListener('click', function(e){
    e.preventDefault();

    const itemId = this.dataset.item;
    const liked = this.dataset.liked == 1;
    const method = liked ? 'DELETE' : 'POST';

    fetch(`/items/${itemId}/like`, {
      method,
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Accept': 'application/json',
      },
    })
    .then(res => res.json())
    .then(data => {
      this.dataset.liked = data.liked ? 1 : 0;

      const count = this.querySelector('.like-count');
      const img = this.querySelector('.like-img');

      if (count) count.textContent = data.count;

      if (img) {
        img.src = data.liked
          ? '{{ asset("images/ハートロゴ_ピンク.png") }}'
          : '{{ asset("images/ハートロゴ_デフォルト.png") }}';
      }
    });
  });
});
</script>

<script>
document.getElementById('comment-form')?.addEventListener('submit', function(e){
  e.preventDefault();

  const itemId = this.dataset.item;
  const input = document.getElementById('comment-input');
  const error = document.getElementById('comment-error');

  fetch(`/items/${itemId}/comments`, {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': '{{ csrf_token() }}',
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({ comment: input.value })
  })
  .then(async res => {
    if (!res.ok) {
      const data = await res.json();
      if (data.errors?.comment) {
        error.textContent = data.errors.comment[0];
        error.style.display = 'block';
      }
      throw new Error('validation');
    }
    return res.json();
  })
  .then(data => {
    input.value = '';
    error.style.display = 'none';
    document.getElementById('comment-list').innerHTML = data.html;
    document.getElementById('comment-count').textContent = data.count;
    document.getElementById('comment-icon-count').textContent = data.count;
  })
  .catch(err => console.warn(err));
});
</script>
@endauth

@endsection