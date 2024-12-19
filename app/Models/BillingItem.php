<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BillingItem extends Model
{
    //
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        'bill_id',
        'product_id',
        'order_quantity',
        'unit_price',
        'total_price',
    ];

    /**
     * Define relationship with Product
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Define relationship with BillingSystem
     */
    public function bill()
    {
        return $this->belongsTo(BillingSystem::class, 'bill_id');
    }
    /*public function calculateItemAmount()
    {
        $product = $this->product;
        if ($product) {
            $this->item_amount = $product->price * $this->order_quantity;
            $this->save();
        }
    }*/

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($billingItem) {
            // Fetch unit price from the Product model
            $product = Product::find($billingItem->product_id);
            $billingItem->unit_price = $product->price; // Assuming 'price' is the column name in the Product model

            // Calculate total price
            $billingItem->total_price = $billingItem->order_quantity * $billingItem->unit_price;
        });
    }
}
