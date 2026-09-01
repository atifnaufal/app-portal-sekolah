<?php

namespace App\Http\Controllers;

use App\Helpers\NotificationHelper;
use App\Models\GlobalPost;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GlobalPortalController extends Controller
{
    public function index(Request $request): View
    {
        $posts = GlobalPost::with(['user.school','school','likes','comments.user'])
            ->latest()->paginate(15);
        $isMobile = (bool) preg_match('/(android|iphone|mobile)/i', $request->userAgent());
        $view = $isMobile ? 'mobile.global-portal' : 'global-portal.index';
        // fallback to mobile if desktop view not exists
        if (! view()->exists($view)) $view='mobile.global-portal';
        return view($view, ['posts'=>$posts, 'schools'=>School::orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $uid = $request->session()->get('user_id');
        $user = \App\Models\User::findOrFail($uid);
        $data = $request->validate([
            'content'=>['required','string','max:2000'],
            'image'=>['nullable','image','mimes:jpg,jpeg,png,webp','max:4096'],
            'school_id'=>['nullable','exists:schools,id'],
        ]);
        $data['user_id']=$uid;
        $data['school_id']=$data['school_id'] ?? $user->school_id;
        if($request->hasFile('image')) $data['image']=$request->file('image')->store('global','public');
        $post = GlobalPost::create($data);
        // notif ringan ke followers global (skip self)
        try{
            NotificationHelper::sendToAll('Portal Global Baru', mb_substr($post->content,0,80), route('global.portal'), 'pengumuman', null, $uid);
        }catch(\Throwable $e){}
        return back()->with('success','Post terkirim ke Global Portal');
    }

    public function toggleLike(Request $request, GlobalPost $post): RedirectResponse
    {
        $uid = $request->session()->get('user_id');
        $like = $post->likes()->where('user_id',$uid)->first();
        if($like){ $like->delete(); $post->decrement('likes_count'); }
        else { $post->likes()->create(['user_id'=>$uid]); $post->increment('likes_count'); }
        return back();
    }

    public function comment(Request $request, GlobalPost $post): RedirectResponse
    {
        $uid = $request->session()->get('user_id');
        $data = $request->validate(['body'=>['required','string','max:500']]);
        $post->comments()->create(['user_id'=>$uid,'body'=>$data['body']]);
        $post->increment('comments_count');
        return back();
    }
}
