<?php

namespace App\Http\Controllers;

use App\Models\BorrowedItem;
use App\Models\Item;
use Illuminate\Http\Request;

class BorrowedItemController extends Controller
{
    public function index(Request $request)
    {
        $adminId = $request->user()->id;

        return BorrowedItem::where('admin_id', $adminId)
                           ->with('item')
                           ->get();
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

        $adminId = $request->user()->id;

        $item = Item::where('id', $request->item_id)
                    ->where('admin_id', $adminId)
                    ->firstOrFail();

        if ($request->status === 'pending' && $item->quantity < $request->quantity) {
            return response()->json(['message' => 'Not enough stock'], 400);
        }

        if ($request->status === 'pending') {
            $item->quantity -= $request->quantity;
            $item->save();
        }

        $borrowedItem = BorrowedItem::create([
            'borrower_name' => $request->borrower_name,
            'item_id' => $item->id,
            'borrowed_date' => $request->borrowed_date,
            'quantity' => $request->quantity,
            'status' => $request->status,
            'admin_id' => $adminId,
        ]);

        return response()->json($borrowedItem, 201);
    }

    public function update(Request $request, $id)
    {
        $adminId = $request->user()->id;

        $borrowedItem = BorrowedItem::where('id', $id)
                                    ->where('admin_id', $adminId)
                                    ->firstOrFail();

        $request->validate([
            'status' => 'required|string|in:pending,returned',
            'borrower_name' => 'sometimes|required|string|max:255',
            'item_id' => 'sometimes|required|integer|exists:items,id',
            'borrowed_date' => 'sometimes|required|date',
            'quantity' => 'sometimes|required|integer|min:1',
        ]);

        $data = $request->all();

        $item = Item::where('id', $borrowedItem->item_id)
                    ->where('admin_id', $adminId)
                    ->first();

        if ($request->status === 'returned' && $item) {
            $data['return_date'] = now();
            $item->quantity += $borrowedItem->quantity;
            $item->save();
        } elseif ($request->status === 'pending' && $item) {
            $data['return_date'] = null;
            $item->quantity -= $borrowedItem->quantity;
            if ($item->quantity < 0) $item->quantity = 0;
            $item->save();
        }

        $borrowedItem->update($data);

        return response()->json($borrowedItem);
    }

    public function destroy(Request $request, $id)
    {
        $adminId = $request->user()->id;

        $borrowedItem = BorrowedItem::where('id', $id)
                                    ->where('admin_id', $adminId)
                                    ->firstOrFail();

        if ($borrowedItem->status === 'pending') {
            $item = Item::where('id', $borrowedItem->item_id)
                        ->where('admin_id', $adminId)
                        ->first();
            if ($item) $item->increment('quantity', $borrowedItem->quantity);
        }

        $borrowedItem->delete();
        return response()->json(['message' => 'Borrow record deleted']);
    }

    public function report(Request $request)
    {
        $adminId = $request->user()->id;

        return [
            'total_items' => Item::where('admin_id', $adminId)->count(),
            'borrowed_count' => BorrowedItem::where('admin_id', $adminId)
                                            ->where('status', 'pending')
                                            ->count(),
            'returned_count' => BorrowedItem::where('admin_id', $adminId)
                                            ->where('status', 'returned')
                                            ->count(),
        ];
    }
}
