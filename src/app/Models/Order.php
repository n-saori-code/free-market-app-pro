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
        'user_id',
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

    public function user()
    {
        return $this->belongsTo(User::class);
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
