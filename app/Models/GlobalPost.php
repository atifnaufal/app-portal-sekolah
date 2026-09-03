<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GlobalPost extends Model
{
    protected $fillable = ['user_id','school_id','content','image','likes_count','comments_count','reports_count','is_hidden'];

    protected function casts(): array
    {
        return ['is_hidden' => 'boolean'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function likes(): HasMany { return $this->hasMany(GlobalLike::class); }
    public function comments(): HasMany { return $this->hasMany(GlobalComment::class)->latest(); }
    public function isLikedBy(int $uid): bool { return $this->likes->contains('user_id',$uid); }
}
