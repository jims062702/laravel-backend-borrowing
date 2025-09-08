<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        return Item::where('admin_id', $request->user()->id)->get();
    }

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

    public function show(Request $request, $id)
    {
        $item = Item::where('id', $id)
                    ->where('admin_id', $request->user()->id)
                    ->firstOrFail();
        return $item;
    }

    public function update(Request $request, $id)
    {
        $item = Item::where('id', $id)
                    ->where('admin_id', $request->user()->id)
                    ->firstOrFail();

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'quantity' => 'sometimes|integer|min:0',
        ]);

        $item->update($request->only(['name', 'description', 'quantity']));
        return $item;
    }

    public function destroy(Request $request, $id)
    {
        $item = Item::where('id', $id)
                    ->where('admin_id', $request->user()->id)
                    ->firstOrFail();
        $item->delete();

        return response()->json(['message' => 'Item deleted']);
    }
}
