<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExhibitionRequest;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Item;

class ItemController extends Controller
{

    public function index(Request $request)
    {
        $tab = $request->query('tab', 'recommend');
        $keyword = $request->query('keyword');

        // マイリストタブ
        if ($tab === 'mylist' && Auth::check()) {

            /** @var \App\Models\User $user */
            $user = Auth::user();

            $items = $user->likedItems()
                ->where('items.user_id', '!=', $user->id)
                ->keyword($keyword)   
                ->get();
        } else {
            // おすすめ（通常一覧）
            $query = Item::latest();

            // 自分の商品は除外
            if (Auth::check()) {
                $query->where('user_id', '!=', Auth::id());
            }

            $items = $query
                ->keyword($keyword)
                ->get();
        }

        return view('items.index', compact('items', 'tab'));
    }


    public function create()
    {   
        $categories = Category::all();

        return view('items.create', compact('categories'));
    }

    public function store(ExhibitionRequest $request)
    {
        // 画像保存
        $path = $request->file('image')->store('items', 'public');
        
        $item = Item::create([
            'user_id' => auth()->id(),
            'status' => $request->status,
            'name' => $request->name,
            'brand' => $request->brand,
            'description' => $request->description,
            'price' => $request->price,
            'image_path' => $path,
        ]);

        $item->categories()->sync($request->category_ids);

        return redirect()->route('mypage', $item)->with('success', '商品を出品しました');

    }

    public function show(Item $item)
    {
        $item->load([
            'categories',
            'comments.user',
            'likes',
        ]);

        return view('items.show',compact('item'));
    }

}
