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
        'admin_id', // add this
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

