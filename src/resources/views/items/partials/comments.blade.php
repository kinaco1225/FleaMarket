@foreach($comments as $comment)
  <div class="comment">
    <div class="comment-user">
      <div class="comment-user-image">
        <img src="{{ optional($comment->user)->profile_image
            ? asset('storage/'.optional($comment->user)->profile_image)
            : asset('images/default-user.png') }}">
      </div>
      <div class="comment-user-name">
        {{ optional($comment->user)->name ?? '退会ユーザー' }}
      </div>
    </div>
    <div class="comment-body">{!! nl2br(e($comment->comment)) !!}</div>
  </div>
@endforeach
