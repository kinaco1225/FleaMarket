<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CommentRequest;
use App\Models\Item;

class CommentController extends Controller
{
    public function store(CommentRequest $request, Item $item)
    {
        $item->comments()->updateOrCreate(
            [
                'user_id' => auth()->id(),
                'item_id' => $item->id,
            ],
            [
                'comment' => $request->comment,
            ]
        );

        $comments = $item->comments()->with('user')->latest()->get();

        return response()->json([
            'html' => view('items.partials.comments', compact('comments'))->render(),
            'count' => $comments->count(),
        ]);
    }
}
