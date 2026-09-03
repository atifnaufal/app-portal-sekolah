<?php

namespace App\Http\Controllers;

use App\Helpers\NotificationHelper;
use App\Helpers\UserContextHelper;
use App\Models\GlobalPost;
use App\Models\GlobalStory;
use App\Models\School;
use App\Models\User;
use App\Services\FirebaseStorageService;
use App\Services\ImageSafetyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class GlobalPortalController extends Controller
{
    public function index(Request $request): View
    {
        $me = UserContextHelper::user($request);
        $isSuper = $me && $me->isSuperAdmin();
        $hasStories = Schema::hasTable('global_stories');
        $hasModeration = Schema::hasColumn('global_posts', 'is_hidden');

        // Postingan tersembunyi (laporan) hanya terlihat Admin Pusat + badge.
        $posts = GlobalPost::with(['user.school', 'user.followers', 'school', 'likes.user', 'comments.user'])
            ->when(! $isSuper && $hasModeration, fn ($q) => $q->where('is_hidden', false))
            ->latest()->paginate(15);

        // Cerita aktif (24 jam), grup per user — versi terbaru tiap user.
        // Privasi: terlihat bila milik sendiri / mengikuti penulis / satu sekolah / admin.
        $followedIds = $me
            ? \App\Models\GlobalFollow::where('follower_id', $me->id)->pluck('followed_id')->all()
            : [];
        $storiesGrouped = $hasStories
            ? GlobalStory::with('user')->active()->latest()->get()->groupBy('user_id')->map(fn ($g) => $g->first())
                ->filter(fn ($st) => $this->canSeeStory($me, $isSuper, $followedIds, $st))
                ->values()
            : collect();
        $myStory = ($me && $hasStories) ? $storiesGrouped->get($me->id) : null;

        // Bersih-bersih oportunistik: hapus cerita kedaluwarsa (max 20).
        try {
            if ($hasStories) {
                GlobalStory::where('expires_at', '<=', now())->limit(20)->delete();
            }
        } catch (\Throwable $e) {
        }

        // Badge aktivitas: like diterima + follower baru + komentar di postingan saya.
        $activityCount = 0;
        if ($me) {
            try {
                $activityCount += \App\Models\GlobalLike::whereHas('post', fn ($q) => $q->where('user_id', $me->id))->count();
                $activityCount += \App\Models\GlobalFollow::where('followed_id', $me->id)->count();
                $activityCount += \App\Models\GlobalComment::whereHas('post', fn ($q) => $q->where('user_id', $me->id))->count();
            } catch (\Throwable $e) {
            }
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
            'activityCount' => $activityCount,
            // JSON viewer disiapkan di controller (ekspresi kompleks di @json merusak kompilasi Blade).
            'storiesJson' => $storiesGrouped->map(fn ($st) => [
                'id' => $st->id,
                'img' => FirebaseStorageService::url($st->image),
                'user' => $st->user->name,
                'avatar' => $st->user->avatar_url,
                'time' => $st->created_at->diffForHumans(),
                'caption' => $st->caption,
            ])->values()->toJson(),
        ]);
    }

    /** Aturan lihat cerita: sendiri > admin (pusat/sekolah ybs) > satu sekolah > mengikuti. */
    public static function canSeeStory(?\App\Models\User $me, bool $isSuper, array $followedIds, GlobalStory $st): bool
    {
        if (! $me) {
            return false;
        }
        if ((int) $st->user_id === (int) $me->id) {
            return true;
        }
        if ($isSuper) {
            return true; // Admin Pusat melihat semua tanpa follow.
        }
        if ($me->role === 'admin' && $me->school_id && (int) $st->school_id === (int) $me->school_id) {
            return true; // Admin sekolah melihat cerita sekolahnya.
        }
        if ($me->school_id && (int) $st->school_id === (int) $me->school_id) {
            return true; // Satu sekolah otomatis saling melihat.
        }

        return in_array((int) $st->user_id, array_map('intval', $followedIds), true);
    }

    /** Auto-follow: user aktif otomatis mengikuti admin sekolahnya + Admin Pusat. */
    public static function autoFollowAdmins(User $user): void
    {
        if (! in_array($user->role, ['guru', 'siswa'], true) || ! $user->aktif) {
            return;
        }
        try {
            $adminIds = User::where('role', 'admin')
                ->where(fn ($q) => $q->where('school_id', $user->school_id)->orWhereNull('school_id'))
                ->pluck('id');
            foreach ($adminIds as $adminId) {
                if ((int) $adminId === (int) $user->id) {
                    continue;
                }
                \App\Models\GlobalFollow::firstOrCreate([
                    'follower_id' => $user->id, 'followed_id' => $adminId,
                ]);
            }
        } catch (\Throwable $e) {
        }
    }

    public function store(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
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
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => $textCheck['reason']], 422);
            }
            return back()->withErrors(['content' => $textCheck['reason']])->withInput();
        }

        // Moderasi gambar — AI bila key tersedia.
        if ($request->hasFile('image')) {
            $imgCheck = ImageSafetyService::checkImage($request->file('image'));
            if (! $imgCheck['safe']) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['ok' => false, 'message' => $imgCheck['reason']], 422);
                }
                return back()->withErrors(['image' => $imgCheck['reason']])->withInput();
            }
            $data['image'] = FirebaseStorageService::put('global', $request->file('image'));
        }

        $post = GlobalPost::create($data);
        // Notif mengarah langsung ke komentar postingan (detail flow).
        try {
            NotificationHelper::sendToAll('Portal Global Baru', mb_substr($post->content, 0, 80), route('global.portal').'#cmt-'.$post->id, 'pengumuman', null, $uid);
        } catch (\Throwable $e) {
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => 'Post terkirim ke Global Portal']);
        }

        return back()->with('success', 'Post terkirim ke Global Portal');
    }

    /** Upload cerita (maks 24 jam tayang). */
    public function storyStore(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $uid = UserContextHelper::id($request);
        $user = UserContextHelper::user($request);
        if (! $user) {
            UserContextHelper::abortUnauthorized($request);
        }
        abort_unless(Schema::hasTable('global_stories'), 503, 'Fitur Cerita belum siap (migrasi berjalan). Coba lagi sebentar.');
        $data = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'caption' => ['nullable', 'string', 'max:200'],
        ]);

        $textCheck = ImageSafetyService::checkText($data['caption'] ?? null);
        if (! $textCheck['safe']) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => $textCheck['reason']], 422);
            }
            return back()->withErrors(['caption' => $textCheck['reason']]);
        }
        $imgCheck = ImageSafetyService::checkImage($request->file('image'));
        if (! $imgCheck['safe']) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => $imgCheck['reason']], 422);
            }
            return back()->withErrors(['image' => $imgCheck['reason']]);
        }

        GlobalStory::create([
            'user_id' => $uid,
            'school_id' => $user->school_id,
            'image' => FirebaseStorageService::put('stories', $request->file('image')),
            'caption' => $data['caption'] ?? null,
            'expires_at' => now()->addDay(),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => 'Cerita dipublikasikan']);
        }

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
    public function report(Request $request, GlobalPost $post): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $uid = UserContextHelper::id($request);
        if (! $uid) {
            UserContextHelper::abortUnauthorized($request);
        }
        abort_if($post->user_id === $uid, 400, 'Tidak bisa melaporkan postingan sendiri.');
        abort_unless(Schema::hasColumn('global_posts', 'reports_count'), 503, 'Fitur Laporan belum siap (migrasi berjalan). Coba lagi sebentar.');

        $post->increment('reports_count');
        $post->refresh();
        if ($post->reports_count >= ImageSafetyService::REPORT_THRESHOLD && ! $post->is_hidden) {
            $post->update(['is_hidden' => true]);
        }

        $msg = $post->is_hidden
            ? 'Terima kasih. Postingan disembunyikan otomatis karena banyak laporan.'
            : 'Laporan diterima. Terima kasih sudah menjaga portal.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => $msg, 'is_hidden' => $post->is_hidden]);
        }

        return back()->with('success', $msg);
    }

    /** Admin Pusat: tampilkan lagi postingan yang disembunyikan. */
    public function unhide(GlobalPost $post): RedirectResponse
    {
        $me = UserContextHelper::user();
        abort_unless($me && $me->isSuperAdmin(), 403);
        $post->update(['is_hidden' => false, 'reports_count' => 0]);

        return back()->with('success', 'Postingan ditampilkan kembali.');
    }

    public function toggleLike(Request $request, GlobalPost $post): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $uid = UserContextHelper::id($request);
        $like = $post->likes()->where('user_id', $uid)->first();
        if ($like) {
            $like->delete();
            GlobalPost::withoutTimestamps(fn () => $post->decrement('likes_count'));
            $liked = false;
        } else {
            $post->likes()->create(['user_id' => $uid]);
            GlobalPost::withoutTimestamps(fn () => $post->increment('likes_count'));
            $liked = true;
        }

        // AJAX (fetch) → JSON agar UI update di tempat tanpa reload/scroll.
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'liked' => $liked,
                'likes_count' => (int) $post->fresh()->likes_count,
            ]);
        }

        return back();
    }

    public function comment(Request $request, GlobalPost $post): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $uid = UserContextHelper::id($request);
        $data = $request->validate(['body' => ['required', 'string', 'max:500']]);

        $textCheck = ImageSafetyService::checkText($data['body']);
        if (! $textCheck['safe']) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => $textCheck['reason']], 422);
            }

            return back()->withErrors(['body' => $textCheck['reason']]);
        }

        $comment = $post->comments()->create(['user_id' => $uid, 'body' => $data['body']]);
        GlobalPost::withoutTimestamps(fn () => $post->increment('comments_count'));

        // AJAX → JSON agar komentar muncul langsung tanpa reload/scroll.
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'comments_count' => (int) $post->fresh()->comments_count,
                'comment' => [
                    'user' => $comment->user->name,
                    'body' => $comment->body,
                    'time' => $comment->created_at->diffForHumans(),
                ],
            ]);
        }

        return back();
    }

    public function toggleFollow(Request $request, \App\Models\User $user): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $uid = UserContextHelper::id($request);
        if ($uid == $user->id) {
            return back();
        }
        $ex = \App\Models\GlobalFollow::where('follower_id', $uid)->where('followed_id', $user->id)->first();
        if ($ex) {
            $ex->delete();
            $following = false;
        } else {
            \App\Models\GlobalFollow::create(['follower_id' => $uid, 'followed_id' => $user->id]);
            $following = true;
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['ok' => true, 'following' => $following]);
        }

        return back();
    }

    /** Polling ringan: jumlah postingan baru setelah ID tertentu (untuk pil "baru"). */
    public function check(Request $request)
    {
        $me = UserContextHelper::user($request);
        $isSuper = $me && $me->isSuperAdmin();
        $after = (int) $request->query('after_id', 0);
        $hasModeration = Schema::hasColumn('global_posts', 'is_hidden');

        $q = GlobalPost::where('id', '>', $after);
        if (! $isSuper && $hasModeration) {
            $q->where('is_hidden', false);
        }

        return response()->json(['new_count' => $q->count()]);
    }

    /** Halaman Aktivitas ala IG: like, follower baru, komentar terbaru. */
    public function activity(Request $request): View
    {
        $me = UserContextHelper::user($request);
        if (! $me) {
            UserContextHelper::abortUnauthorized($request);
        }

        $likes = \App\Models\GlobalLike::with(['user', 'post'])
            ->whereHas('post', fn ($q) => $q->where('user_id', $me->id))
            ->latest()->take(20)->get();
        $followers = \App\Models\GlobalFollow::with('follower')
            ->where('followed_id', $me->id)
            ->latest()->take(20)->get();
        $comments = \App\Models\GlobalComment::with(['user', 'post'])
            ->whereHas('post', fn ($q) => $q->where('user_id', $me->id))
            ->latest()->take(20)->get();

        $isMobile = (bool) preg_match('/(android|iphone|mobile)/i', $request->userAgent());
        $view = $isMobile ? 'mobile.global-activity' : 'global-portal.activity';
        if (! view()->exists($view)) {
            $view = 'mobile.global-activity';
        }

        return view($view, compact('likes', 'followers', 'comments', 'me'));
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
