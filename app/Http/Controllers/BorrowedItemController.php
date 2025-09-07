<?php

namespace App\Http\Controllers;

use App\Models\BorrowedItem;
use App\Models\Item;
use Illuminate\Http\Request;

class BorrowedItemController extends Controller
{
    public function index()
    {
        return BorrowedItem::with('item')->get();
    }

    public function store(Request $request)
{
    $request->validate([
        'borrower_name' => 'required|string|max:255',
        'item_id' => 'required|integer|exists:items,id',
        'borrowed_date' => 'required|date',
        'quantity' => 'required|integer|min:1',
        'status' => 'required|string|in:pending,returned',
    ]);

    $item = Item::findOrFail($request->item_id);

    if ($request->status === 'pending') {
        if ($item->quantity < $request->quantity) {
            return response()->json(['message' => 'Not enough stock available'], 400);
        }
        $item->quantity -= $request->quantity;
        $item->save();
    }

    $borrowedItem = BorrowedItem::create($request->all());

    return response()->json($borrowedItem, 201);
}


    public function update(Request $request, $id)
{
    $borrowedItem = BorrowedItem::findOrFail($id);

    $request->validate([
        'status' => 'required|string|in:pending,returned',
        'borrower_name' => 'sometimes|required|string|max:255',
        'item_id' => 'sometimes|required|integer|exists:items,id',
        'borrowed_date' => 'sometimes|required|date',
        'quantity' => 'sometimes|required|integer|min:1',
    ]);

    $data = $request->all();

    if ($request->status === 'returned') {
        $data['return_date'] = now();

        // ✅ Add back quantity to items table
        $item = Item::find($borrowedItem->item_id);
        if ($item) {
            $item->quantity += $borrowedItem->quantity;
            $item->save();
        }
    } elseif ($request->status === 'pending') {
        $data['return_date'] = null;

        // ✅ If changed back to pending, subtract quantity again
        $item = Item::find($borrowedItem->item_id);
        if ($item) {
            $item->quantity -= $borrowedItem->quantity;
            if ($item->quantity < 0) {
                $item->quantity = 0; // prevent negative
            }
            $item->save();
        }
    }

    $borrowedItem->update($data);

    return response()->json($borrowedItem);
}





    public function destroy(BorrowedItem $borrowedItem)
    {
        if ($borrowedItem->status === 'pending') {
            $item = Item::find($borrowedItem->item_id);
            $item->increment('quantity', $borrowedItem->quantity);
        }

        $borrowedItem->delete();
        return response()->json(['message' => 'Borrow record deleted']);
    }

    public function report()
    {
        return [
            'total_items' => Item::count(),
            'borrowed_count' => BorrowedItem::where('status', 'pending')->count(),
            'returned_count' => BorrowedItem::where('status', 'returned')->count(),
        ];
    }
}
