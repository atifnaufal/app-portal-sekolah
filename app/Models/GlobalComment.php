<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class GlobalComment extends Model {
    protected $fillable=['global_post_id','user_id','body'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function post(): BelongsTo { return $this->belongsTo(GlobalPost::class,'global_post_id'); }
}
