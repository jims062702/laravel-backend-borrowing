<?php

namespace App\Http\Controllers;

use App\Models\BorrowedItem;
use App\Models\Item;
use Illuminate\Http\Request;

class BorrowedItemController extends Controller
{
    // List borrowed items for current admin only
    public function index(Request $request)
    {
        return BorrowedItem::where('admin_id', $request->user()->id)
                           ->with('item')
                           ->get();
    }

    // Store a new borrowed item for current admin
    public function store(Request $request)
    {
        $request->validate([
            'borrower_name' => 'required|string|max:255',
            'item_id' => 'required|integer|exists:items,id',
            'borrowed_date' => 'required|date',
            'quantity' => 'required|integer|min:1',
            'status' => 'required|string|in:pending,returned',
        ]);

        // Ensure the item belongs to current admin
        $item = Item::where('id', $request->item_id)
                    ->where('admin_id', $request->user()->id)
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
            'admin_id' => $request->user()->id,
        ]);

        return response()->json($borrowedItem, 201);
    }

    // Update borrowed item (only for current admin)
    public function update(Request $request, $id)
    {
        $borrowedItem = BorrowedItem::where('id', $id)
                                    ->where('admin_id', $request->user()->id)
                                    ->firstOrFail();

        $request->validate([
            'status' => 'required|string|in:pending,returned',
            'borrower_name' => 'sometimes|required|string|max:255',
            'item_id' => 'sometimes|required|integer|exists:items,id',
            'borrowed_date' => 'sometimes|required|date',
            'quantity' => 'sometimes|required|integer|min:1',
        ]);

        $data = $request->only(['borrower_name','item_id','borrowed_date','quantity','status']);

        // Handle stock adjustment
        if ($request->status === 'returned' && $borrowedItem->status !== 'returned') {
            $data['return_date'] = now();
            $item = Item::find($borrowedItem->item_id);
            if ($item) {
                $item->quantity += $borrowedItem->quantity;
                $item->save();
            }
        } elseif ($request->status === 'pending' && $borrowedItem->status === 'returned') {
            $data['return_date'] = null;
            $item = Item::find($borrowedItem->item_id);
            if ($item) {
                $item->quantity -= $borrowedItem->quantity;
                if ($item->quantity < 0) $item->quantity = 0;
                $item->save();
            }
        }

        $borrowedItem->update($data);

        return response()->json($borrowedItem);
    }

    // Delete borrowed item (only for current admin)
    public function destroy(Request $request, $id)
    {
        $borrowedItem = BorrowedItem::where('id', $id)
                                    ->where('admin_id', $request->user()->id)
                                    ->firstOrFail();

        if ($borrowedItem->status === 'pending') {
            $item = Item::find($borrowedItem->item_id);
            if ($item) $item->quantity += $borrowedItem->quantity;
        }

        $borrowedItem->delete();
        return response()->json(['message' => 'Borrow record deleted']);
    }

    // Reports for current admin only
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
