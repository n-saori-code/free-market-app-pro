<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Product;

class FavoriteButton extends Component
{
    public $product;
    public $favoriteCount;

    public function mount(Product $product)
    {
        $this->product = $product;
        $this->favoriteCount = $product->favoritedByUsers()->count();
    }

    public function toggleFavorite()
    {
        // ログイン判定なしなので、個別のON/OFFはなし
        // ここでは総数だけを増やす（疑似的に）
        $this->favoriteCount++;
    }

    public function render()
    {
        return view('livewire.favorite-button');
    }
}
