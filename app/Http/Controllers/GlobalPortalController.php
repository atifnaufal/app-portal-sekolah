<?php

namespace App\Http\Controllers;

use App\Helpers\NotificationHelper;
use App\Helpers\UserContextHelper;
use App\Models\GlobalPost;
use App\Models\GlobalStory;
use App\Models\School;
use App\Services\FirebaseStorageService;
use App\Services\ImageSafetyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GlobalPortalController extends Controller
{
    public function index(Request $request): View
    {
        $me = UserContextHelper::user($request);
        $isSuper = $me && $me->isSuperAdmin();

        // Postingan tersembunyi (laporan) hanya terlihat Admin Pusat + badge.
        $posts = GlobalPost::with(['user.school', 'user.followers', 'school', 'likes.user', 'comments.user'])
            ->when(! $isSuper, fn ($q) => $q->where('is_hidden', false))
            ->latest()->paginate(15);

        // Cerita aktif (24 jam), grup per user — versi terbaru tiap user.
        $storiesGrouped = GlobalStory::with('user')
            ->active()->latest()
            ->get()->groupBy('user_id')
            ->map(fn ($g) => $g->first());
        $myStory = $me ? $storiesGrouped->get($me->id) : null;

        // Bersih-bersih oportunistik: hapus cerita kedaluwarsa (max 20).
        try {
            GlobalStory::where('expires_at', '<=', now())->limit(20)->delete();
        } catch (\Throwable $e) {
        }

        $isMobile = (bool) preg_match('/(android|iphone|mobile)/i', $request->userAgent());
        $view = $isMobile ? 'mobile.global-portal' : 'global-portal.index';
        if (! view()->exists($view)) {
            $view = 'mobile.global-portal';
        }

        return view($view, [
            'posts' => $posts,
            'schools' => School::orderBy('name')->get(),
            'me' => $me,
            'isSuper' => $isSuper,
            'storiesGrouped' => $storiesGrouped,
            'myStory' => $myStory,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $uid = UserContextHelper::id($request);
        $user = UserContextHelper::user($request);
        if (! $user) {
            UserContextHelper::abortUnauthorized($request);
        }
        $data = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        // Sekolah postingan OTOMATIS dari akun (tanpa dropdown).
        $data['user_id'] = $uid;
        $data['school_id'] = $user->school_id;

        // Moderasi teks — selalu jalan.
        $textCheck = ImageSafetyService::checkText($data['content']);
        if (! $textCheck['safe']) {
            return back()->withErrors(['content' => $textCheck['reason']])->withInput();
        }

        // Moderasi gambar — AI bila key tersedia.
        if ($request->hasFile('image')) {
            $imgCheck = ImageSafetyService::checkImage($request->file('image'));
            if (! $imgCheck['safe']) {
                return back()->withErrors(['image' => $imgCheck['reason']])->withInput();
            }
            $data['image'] = FirebaseStorageService::put('global', $request->file('image'));
        }

        $post = GlobalPost::create($data);
        // notif ringan ke followers global (skip self)
        try {
            NotificationHelper::sendToAll('Portal Global Baru', mb_substr($post->content, 0, 80), route('global.portal'), 'pengumuman', null, $uid);
        } catch (\Throwable $e) {
        }

        return back()->with('success', 'Post terkirim ke Global Portal');
    }

    /** Upload cerita (maks 24 jam tayang). */
    public function storyStore(Request $request): RedirectResponse
    {
        $uid = UserContextHelper::id($request);
        $user = UserContextHelper::user($request);
        if (! $user) {
            UserContextHelper::abortUnauthorized($request);
        }
        $data = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'caption' => ['nullable', 'string', 'max:200'],
        ]);

        $textCheck = ImageSafetyService::checkText($data['caption'] ?? null);
        if (! $textCheck['safe']) {
            return back()->withErrors(['caption' => $textCheck['reason']]);
        }
        $imgCheck = ImageSafetyService::checkImage($request->file('image'));
        if (! $imgCheck['safe']) {
            return back()->withErrors(['image' => $imgCheck['reason']]);
        }

        GlobalStory::create([
            'user_id' => $uid,
            'school_id' => $user->school_id,
            'image' => FirebaseStorageService::put('stories', $request->file('image')),
            'caption' => $data['caption'] ?? null,
            'expires_at' => now()->addDay(),
        ]);

        return back()->with('success', 'Cerita dipublikasikan (tayang 24 jam).');
    }

    public function storyDestroy(Request $request, GlobalStory $story): RedirectResponse
    {
        $me = UserContextHelper::user($request);
        abort_unless($me && ($me->id === $story->user_id || $me->isSuperAdmin()), 403);
        $story->delete();

        return back()->with('success', 'Cerita dihapus.');
    }

    /** Laporkan postingan. Otomatis sembunyi saat mencapai ambang. */
    public function report(Request $request, GlobalPost $post): RedirectResponse
    {
        $uid = UserContextHelper::id($request);
        if (! $uid) {
            UserContextHelper::abortUnauthorized($request);
        }
        abort_if($post->user_id === $uid, 400, 'Tidak bisa melaporkan postingan sendiri.');

        $post->increment('reports_count');
        $post->refresh();
        if ($post->reports_count >= ImageSafetyService::REPORT_THRESHOLD && ! $post->is_hidden) {
            $post->update(['is_hidden' => true]);
        }

        return back()->with('success', $post->is_hidden
            ? 'Terima kasih. Postingan disembunyikan otomatis karena banyak laporan.'
            : 'Laporan diterima. Terima kasih sudah menjaga portal.');
    }

    /** Admin Pusat: tampilkan lagi postingan yang disembunyikan. */
    public function unhide(GlobalPost $post): RedirectResponse
    {
        $me = UserContextHelper::user();
        abort_unless($me && $me->isSuperAdmin(), 403);
        $post->update(['is_hidden' => false, 'reports_count' => 0]);

        return back()->with('success', 'Postingan ditampilkan kembali.');
    }

    public function toggleLike(Request $request, GlobalPost $post): RedirectResponse
    {
        $uid = UserContextHelper::id($request);
        $like = $post->likes()->where('user_id', $uid)->first();
        if ($like) {
            $like->delete();
            GlobalPost::withoutTimestamps(fn () => $post->decrement('likes_count'));
        } else {
            $post->likes()->create(['user_id' => $uid]);
            GlobalPost::withoutTimestamps(fn () => $post->increment('likes_count'));
        }

        return back();
    }

    public function comment(Request $request, GlobalPost $post): RedirectResponse
    {
        $uid = UserContextHelper::id($request);
        $data = $request->validate(['body' => ['required', 'string', 'max:500']]);

        $textCheck = ImageSafetyService::checkText($data['body']);
        if (! $textCheck['safe']) {
            return back()->withErrors(['body' => $textCheck['reason']]);
        }

        $post->comments()->create(['user_id' => $uid, 'body' => $data['body']]);
        GlobalPost::withoutTimestamps(fn () => $post->increment('comments_count'));

        return back();
    }

    public function toggleFollow(Request $request, \App\Models\User $user): RedirectResponse
    {
        $uid = UserContextHelper::id($request);
        if ($uid == $user->id) {
            return back();
        }
        $ex = \App\Models\GlobalFollow::where('follower_id', $uid)->where('followed_id', $user->id)->first();
        if ($ex) {
            $ex->delete();
        } else {
            \App\Models\GlobalFollow::create(['follower_id' => $uid, 'followed_id' => $user->id]);
        }

        return back();
    }

    public function profile(Request $request, \App\Models\User $user): View
    {
        $posts = \App\Models\GlobalPost::where('user_id', $user->id)->latest()->paginate(12);
        $isFollowing = \App\Models\GlobalFollow::where('follower_id', UserContextHelper::id($request))->where('followed_id', $user->id)->exists();
        $isMobile = (bool) preg_match('/(android|iphone|mobile)/i', $request->userAgent());
        $view = $isMobile ? 'mobile.global-profile' : 'global-portal.profile';
        if (! view()->exists($view)) {
            $view = 'mobile.global-profile';
        }

        return view($view, ['profileUser' => $user->load(['school', 'followers', 'following']), 'posts' => $posts, 'isFollowing' => $isFollowing]);
    }
}
