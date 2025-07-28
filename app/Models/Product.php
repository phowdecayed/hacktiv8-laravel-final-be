<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = ['name', 'description', 'price', 'category_id', 'user_id'];

    /**
     * Get the images for the product.
     */
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Get the category for the product.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the user who created this product.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all transactions for this product.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
