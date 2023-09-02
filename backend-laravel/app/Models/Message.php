<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'conversation_id', 'user_id', 'body', 'type',
    ];

    protected $casts = [
        'body' => 'json',
    ];

    protected $appends = [
        'attachment_url'
    ];
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withDefault([
            'name' => 'User'
        ]);
    }

    public function recipients()
    {
        return $this->belongsToMany(User::class, 'recipients')
            ->withPivot([
                'read_at', 'deleted_at',
            ]);
    }

    public function getAttachmentUrlAttribute()
    {
        if ($this->type === 'attachment') {
            return asset('storage/' . $this->body['file_path']);
        }
        return null;
    }
//    public function getBodyAttribute()
//    {
//        if ($this->body) {
//            $value = $this->body;
//            return [
//                'file_name' => $value['file_name'],
//                'file_size' => $value['file_size'],
//                'mimetype' => $value['mimetype'],
//                'file_path' => asset('storage/' . $value['filePath']),
//            ];
//        }
//        return null;
//    }
}
