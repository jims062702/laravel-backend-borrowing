<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BorrowedItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'borrower_name',
        'item_id',
        'borrowed_date',
        'quantity',
        'status',
        'return_date',
        'admin_id',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
