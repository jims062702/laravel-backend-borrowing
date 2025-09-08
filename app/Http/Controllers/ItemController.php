<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    // List items for the current admin only
    public function index(Request $request)
    {
        $adminId = $request->user()->id;
        return Item::where('admin_id', $adminId)->get();
    }

    // Store a new item for the current admin
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'quantity' => 'required|integer|min:0',
        ]);

        $item = Item::create([
            'name' => $request->name,
            'description' => $request->description,
            'quantity' => $request->quantity,
            'admin_id' => $request->user()->id, // assign current admin
        ]);

        return response()->json($item, 201);
    }

    // Show an item if it belongs to the current admin
    public function show(Request $request, $id)
    {
        $adminId = $request->user()->id;

        $item = Item::where('id', $id)
                    ->where('admin_id', $adminId)
                    ->firstOrFail();

        return $item;
    }

    // Update an item only if it belongs to the current admin
    public function update(Request $request, $id)
    {
        $adminId = $request->user()->id;

        $item = Item::where('id', $id)
                    ->where('admin_id', $adminId)
                    ->firstOrFail();

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'quantity' => 'sometimes|integer|min:0',
        ]);

        $item->update($request->only(['name', 'description', 'quantity']));

        return response()->json($item);
    }

    // Delete an item only if it belongs to the current admin
    public function destroy(Request $request, $id)
    {
        $adminId = $request->user()->id;

        $item = Item::where('id', $id)
                    ->where('admin_id', $adminId)
                    ->firstOrFail();

        $item->delete();

        return response()->json(['message' => 'Item deleted successfully']);
    }
}
