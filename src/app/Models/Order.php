<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Product;
use App\Models\Review;
use App\Models\Message;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id',
        'seller_id',
        'product_id',
        'postal_code',
        'address',
        'building',
        'payment_method',
        'status',
    ];

    const STATUS_PENDING   = 'pending';
    const STATUS_IN_CHAT   = 'in_chat';
    const STATUS_COMPLETED = 'completed';

    // 購入者
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    // 出品者
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
