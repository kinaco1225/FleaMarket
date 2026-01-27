<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;

class LikeController extends Controller
{
    public function store(Item $item)
    {
        $item->likedUsers()->syncWithoutDetaching(auth()->id());

        return response()->json([
            'liked' => true,
            'count' => $item->likedUsers()->count(),
        ]);
    }

    public function destroy(Item $item)
    {
        $item->likedUsers()->detach(auth()->id());

        return response()->json([
            'liked' => false,
            'count' => $item->likedUsers()->count(),
        ]);
    }
}
