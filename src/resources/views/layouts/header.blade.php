<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>@yield('title', 'フリマアプリ')</title>
  <link rel="stylesheet" href="{{asset('css/sanitize.css')}}?v={{ time() }}">
  <link rel="stylesheet" href="{{asset('css/header.css')}}?v={{ time() }}">
  @yield('css')  
</head>
  
<body>
  <header class="site-header">
  <div class="header-inner">

    <div class="header-left">
      <a href="{{ route('items.index') }}">
        <img src="{{ asset('images/COACHTECHヘッダーロゴ.png') }}" alt="COACHTECH" class="header-logo">
      </a>
    </div>

    <div class="header-center">
      <form action="/" method="get">
        <input type="hidden" name="tab" value="{{ request('tab', 'recommend') }}">
        <input type="text" name="keyword" value="{{ request('keyword')}}" placeholder="なにをお探しですか？" class="header-search">
      </form>
    </div>

    <div class="header-right">

      @auth
        <form action="{{ route('logout') }}" method="POST" class="inline-form">
          @csrf
          <button type="submit" class="header-link">ログアウト</button>
        </form>

        <a href="{{ route('mypage') }}" class="header-link">マイページ</a>

        <a href="{{ route('items.create') }}" class="header-button">出品</a>
      @endauth
      
      @guest
        <a href="{{ route('login') }}" class="header-link">ログイン</a>

        <a href="{{ route('login') }}" class="header-link">マイページ</a>

        <a href="{{ route('login') }}"  class="header-button">出品</a>
      @endguest

    </div>

  </div>
</header>
  <main>
    @yield('content')
  </main>
  @yield('js')
</body>
</html>









