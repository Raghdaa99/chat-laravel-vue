<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = ['user_id', 'label', 'type', 'last_message_id'];

    protected $casts = [
        'label' => 'json',
    ];

    protected $appends = [
        'image_url'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'conversation_id', 'id');
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'participants')
            ->withPivot([
                'joined_at', 'role'
            ]);
    }

    public function recipients()
    {
        return $this->hasManyThrough(
            Recipient::class,
            Message::class,
            'conversation_id',
            'message_id',
            'id',
            'id'
        );
    }

    public function lastMessage()
    {
        return $this->belongsTo(Message::class, 'last_message_id', 'id')
            ->whereNull('deleted_at')
            ->withDefault([
                'body' => 'Message deleted'
            ]);
    }

    public function getImageUrlAttribute()
    {
        if ($this->type === 'group') {
            if ($this->label) {
                if ($this->label['image']) {
                    return asset('storage/' . $this->label['image']['file_path']);
                } else {
                    return asset('assets/img/group/teamwork.png');
                }

            } else {
                return '@/assets/img/group/teamwork.png';
            }
        }
        return $this->participants[0]->avatar_url;
//        return conversation . participants[0] . avatar_url;
    }


}
