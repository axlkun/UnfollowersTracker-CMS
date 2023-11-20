<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Data extends Model
{
    use HasFactory;

    protected $table = 'api_data';

    protected $fillable = [
        'usuario_id',
        'close_friends',
        'followers',
        'following',
        'hide_story_from',
        'pending_follow_requests',
        'recent_follow_requests',
        'recently_unfollowed_accounts',
        'removed_suggestions',
    ];

    // Castear los campos JSON a arrays asociativos
    protected $casts = [
        'close_friends' => 'json',
        'followers' => 'json',
        'following' => 'json',
        'hide_story_from' => 'json',
        'pending_follow_requests' => 'json',
        'recent_follow_requests' => 'json',
        'recently_unfollowed_accounts' => 'json',
        'removed_suggestions' => 'json',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
