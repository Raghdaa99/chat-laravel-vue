<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Events\OnlineStatusUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_online'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = [
        'avatar_url',
    ];


    protected $dispatchesEvents = [
        'updated' => OnlineStatusUpdated::class,
    ];

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'participants')
            ->latest('last_message_id')
            ->withPivot([
                'role', 'joined_at'
            ]);
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'user_id', 'id');
    }

    public function receivedMessages()
    {
        return $this->belongsToMany(Message::class, 'recipients')
            ->withPivot([
                'read_at', 'deleted_at',
            ]);
    }

    public function getAvatarUrlAttribute()
    {
        $src = 'https://ui-avatars.com/api/?background=random&name=' . $this->name;
        if ($this->image) {
            $src = asset('storage/' . $this->image);
        }

        return $src;
    }

//    protected function AvatarUrl(): Attribute
//    {
//        return Attribute::make(
//            get: function () {
//                $src = 'https://ui-avatars.com/api/?background=random&name=' . $this->name;
//                if ($this->image) {
//                    $src = asset('storage/' . $this->image);
//                }
//
//                return $src;
//            },
//        );
//    }


   public function isInConversation($conversationId)
    {

        return $this->conversations()->where('conversation_id', $conversationId)->exists();
    }

    public function checkIfAdmin($conversationId)
    {
        $conversation = $this->conversations()->findOrFail($conversationId);

        // Check if the conversation is of type "group"
        if ($conversation->type === 'group') {
            // Check if the current user has the role of "admin" among participants
            return $conversation->pivot->role === 'admin';
        }

        return false;
    }
}
