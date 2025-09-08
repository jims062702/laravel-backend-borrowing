<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    // List items for logged-in admin
    public function index(Request $request)
    {
        $items = Item::where('admin_id', $request->user()->id)->get();
        return response()->json($items);
    }

    // Create item
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
            'admin_id' => $request->user()->id,
        ]);

        return response()->json($item, 201);
    }

    // Optional: show single item
    public function show(Request $request, $id)
    {
        $item = Item::where('admin_id', $request->user()->id)->findOrFail($id);
        return response()->json($item);
    }

    // Optional: update item
    public function update(Request $request, $id)
    {
        $item = Item::where('admin_id', $request->user()->id)->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'quantity' => 'required|integer|min:0',
        ]);

        $item->update($request->only('name', 'description', 'quantity'));

        return response()->json($item);
    }

    // Optional: delete item
    public function destroy(Request $request, $id)
    {
        $item = Item::where('admin_id', $request->user()->id)->findOrFail($id);
        $item->delete();

        return response()->json(['message' => 'Item deleted']);
    }
}
