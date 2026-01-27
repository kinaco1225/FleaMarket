<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $likedUsers
 * @method BelongsToMany likedUsers()
 */

class Item extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'status',
        'name',
        'brand',
        'description',
        'price',
        'image_path',
        'is_sold'
    ];

    const STATUS_GOOD = 1;
    const STATUS_OK = 2;
    const STATUS_USED = 3;
    const STATUS_BAD = 4;

    public static function statusLabels(): array
    {
        return [
            self::STATUS_GOOD => '良好',
            self::STATUS_OK => '目立った傷や汚れなし',
            self::STATUS_USED => 'やや傷や汚れあり',
            self::STATUS_BAD => '状態が悪い',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabels()[$this->status] ?? '-';
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function likes()
    {
        return $this->belongsToMany(Item::class, 'likes');
    }
    public function likedUsers()
    {
        return $this->belongsToMany(User::class, 'likes');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function scopeKeyword($query, $keyword)
    {
        if (!empty($keyword)) {
            $query->where('name', 'like', '%' . $keyword . '%');
        }

        return $query;
    }
    
}
