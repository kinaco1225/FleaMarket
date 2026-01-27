<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use App\Models\Item;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\Address;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $page = $request->query('page', 'sell');

        if ($page === 'buy') {
            $items = $user->purchases()
                ->with('item')
                ->get()
                ->pluck('item');
        } else {
            $items = Item::where('user_id', $user->id)
                ->latest()
                ->get();
        }

        return view('mypage.mypage', compact('user', 'items', 'page'));
    }

    public function edit()
    {
        $user = Auth::user();
        return view('mypage.profile', compact('user'));
    }

    public function update(ProfileRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->id !== Auth::id()) {
            abort(403);
        }

        $wasCompleted = $user->is_profile_completed;

        $data = $request->only(['name', 'postal_code', 'address', 'building']);

        if ($request->hasFile('profile_image')) {

            // ① 既存画像があれば削除
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }

            // ② 新しい画像を保存
            $data['profile_image'] = $request->file('profile_image')->store('profiles', 'public');
        }

        // 初回完了フラグを立てる
        $data['is_profile_completed'] = true;

        $user->update($data);

        // 🔹 初回だけ TOP へ
        if (! $wasCompleted) {
            return redirect()->route('items.index')
                ->with('success', 'プロフィールを登録しました');
        }

        // 🔹 2回目以降はマイページ
        return redirect()->route('mypage')
            ->with('success', 'プロフィールを更新しました');
    }
}
