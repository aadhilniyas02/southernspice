<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BillingSystem extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        'bill_date',
        'bill_time',
        'total_amount',
    ];

    public function updateInventory()
    {
        foreach ($this->billingItems as $item) {
            // Find the product and its related inventory
            $product = $item->product;

            if ($product) {
                foreach ($product->productComponents as $component) {
                    $inventory = $component->inventory;

                    if ($inventory) {
                        // Decrease the inventory quantity based on the quantity required for the product
                        $quantityUsed = $component->quantity_required * $item->order_quantity;
                        $inventory->decreaseQuantity($quantityUsed);
                    }
                    // Check if inventory level is below or equal to the trigger level
                    
                }
            }
        }
    }

    /**
     * Define the relationship to BillingItem.
     * Each bill can have multiple items/products.
     */
    public function billingItems()
    {
        return $this->hasMany(BillingItem::class, 'bill_id');
    }

    protected static function booted()
    {
        static::saved(function ($billingSystem) {
            // Check if the total amount is different before updating
            $totalAmount = $billingSystem->billingItems->sum('total_price');

            if ($billingSystem->total_amount != $totalAmount) {
                // Use updateQuietly to avoid triggering the saved event again
                $billingSystem->updateQuietly(['total_amount' => $totalAmount]);
            }
            // Call the updateInventory method to adjust inventory

            $billingSystem->updateInventory();
        });
    }
}
