<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BorrowedItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'borrower_name',
        'borrowed_date',
        'quantity',
        'status',
        'return_date',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
