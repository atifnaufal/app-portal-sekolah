<?php

namespace App\Http\Controllers;

use App\Helpers\NotificationHelper;
use App\Helpers\UserContextHelper;
use App\Models\GlobalPost;
use App\Models\School;
use App\Services\FirebaseStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GlobalPortalController extends Controller
{
    public function index(Request $request): View
    {
        $posts = GlobalPost::with(['user.school','user.followers','school','likes.user','comments.user'])
            ->latest()->paginate(15);
        $isMobile = (bool) preg_match('/(android|iphone|mobile)/i', $request->userAgent());
        $view = $isMobile ? 'mobile.global-portal' : 'global-portal.index';
        if (! view()->exists($view)) $view='mobile.global-portal';
        $me = UserContextHelper::user($request);
        return view($view, ['posts'=>$posts, 'schools'=>School::orderBy('name')->get(), 'me'=>$me]);
    }

    public function store(Request $request): RedirectResponse
    {
        $uid = UserContextHelper::id($request);
        $user = UserContextHelper::user($request);
        if (! $user) {
            UserContextHelper::abortUnauthorized($request);
        }
        $data = $request->validate([
            'content'=>['required','string','max:2000'],
            'image'=>['nullable','image','mimes:jpg,jpeg,png,webp','max:4096'],
            'school_id'=>['nullable','exists:schools,id'],
        ]);
        $data['user_id']=$uid;
        $data['school_id']=$data['school_id'] ?? $user->school_id;
        if($request->hasFile('image')) $data['image']=FirebaseStorageService::put('global', $request->file('image'));
        $post = GlobalPost::create($data);
        // notif ringan ke followers global (skip self)
        try{
            NotificationHelper::sendToAll('Portal Global Baru', mb_substr($post->content,0,80), route('global.portal'), 'pengumuman', null, $uid);
        }catch(\Throwable $e){}
        return back()->with('success','Post terkirim ke Global Portal');
    }

    public function toggleLike(Request $request, GlobalPost $post): RedirectResponse
    {
        $uid = UserContextHelper::id($request);
        $like = $post->likes()->where('user_id',$uid)->first();
        if($like){ $like->delete(); GlobalPost::withoutTimestamps(fn() => $post->decrement('likes_count')); }
        else { $post->likes()->create(['user_id'=>$uid]); GlobalPost::withoutTimestamps(fn() => $post->increment('likes_count')); }
        return back();
    }

    public function comment(Request $request, GlobalPost $post): RedirectResponse
    {
        $uid = UserContextHelper::id($request);
        $data = $request->validate(['body'=>['required','string','max:500']]);
        $post->comments()->create(['user_id'=>$uid,'body'=>$data['body']]);
        GlobalPost::withoutTimestamps(fn() => $post->increment('comments_count'));
        return back();
    }

    public function toggleFollow(Request $request, \App\Models\User $user): RedirectResponse
    {
        $uid = UserContextHelper::id($request);
        if($uid==$user->id) return back();
        $ex=\App\Models\GlobalFollow::where('follower_id',$uid)->where('followed_id',$user->id)->first();
        if($ex) $ex->delete(); else \App\Models\GlobalFollow::create(['follower_id'=>$uid,'followed_id'=>$user->id]);
        return back();
    }

    public function profile(Request $request, \App\Models\User $user): View
    {
        $posts = \App\Models\GlobalPost::where('user_id',$user->id)->latest()->paginate(12);
        $isFollowing = \App\Models\GlobalFollow::where('follower_id', UserContextHelper::id($request))->where('followed_id',$user->id)->exists();
        $isMobile = (bool) preg_match('/(android|iphone|mobile)/i', $request->userAgent());
        $view = $isMobile ? 'mobile.global-profile' : 'global-portal.profile';
        if (! view()->exists($view)) $view = 'mobile.global-profile';
        return view($view, ['profileUser'=>$user->load(['school','followers','following']), 'posts'=>$posts, 'isFollowing'=>$isFollowing]);
    }
}
