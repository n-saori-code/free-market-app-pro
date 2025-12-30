<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Product;
use App\Models\Order;
use App\Models\Review;
use App\Models\Message;

class User extends Authenticatable implements MustVerifyEmail
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


    // リレーション
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // 購入者としての注文
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // 出品者としての取引
    public function soldOrders()
    {
        return $this->hasManyThrough(Order::class, Product::class);
    }

    public function favoriteProducts()
    {
        return $this->belongsToMany(Product::class, 'favorites')
            ->withTimestamps();
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // 自分が書いたレビュー
    public function writtenReviews()
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    // 自分が受けたレビュー
    public function receivedReviews()
    {
        return $this->hasMany(Review::class, 'reviewed_id');
    }

    // 自分が送信したメッセージ
    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }
}
