<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StoreInventoryItemRequest;
use App\Http\Requests\Inventory\StoreInventoryMovementRequest;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function items(Request $request): View
    {
        $this->authorize('viewAny', InventoryItem::class);

        $items = InventoryItem::query()
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->string('q').'%'))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('inventory.items', ['items' => $items]);
    }

    public function lowStock(Request $request): View
    {
        $this->authorize('viewAny', InventoryItem::class);

        $items = InventoryItem::query()
            ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
            ->orderBy('name')
            ->paginate(50);

        $valuation = (clone $items)->getCollection()->sum(fn (InventoryItem $i) => (float) $i->unit_cost * (int) $i->quantity_on_hand);

        return view('inventory.low_stock', [
            'items' => $items,
            'valuation' => $valuation,
        ]);
    }

    public function movements(Request $request): View
    {
        $this->authorize('viewAny', InventoryItem::class);

        $movements = InventoryMovement::query()
            ->with(['item', 'performer'])
            ->orderByDesc('id')
            ->paginate(50);

        return view('inventory.movements', ['movements' => $movements]);
    }

    public function storeItem(StoreInventoryItemRequest $request): RedirectResponse
    {
        $data = $request->validated();
        InventoryItem::query()->create([
            'tenant_id' => (int) $request->user()->tenant_id,
            'name' => $data['name'],
            'category' => $data['category'] ?? null,
            'sku' => $data['sku'] ?? null,
            'quantity_on_hand' => $data['quantity_on_hand'],
            'reorder_level' => $data['reorder_level'] ?? 0,
            'unit_cost' => $data['unit_cost'] ?? null,
            'location' => $data['location'] ?? null,
            'status' => InventoryItem::STATUS_ACTIVE,
        ]);

        return redirect()->route('inventory.items')->with('status', __('Item added.'));
    }

    public function storeMovement(StoreInventoryMovementRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $request): void {
            $item = InventoryItem::query()->whereKey($data['inventory_item_id'])->lockForUpdate()->firstOrFail();
            $qty = (int) $data['quantity'];
            $type = $data['movement_type'];

            $newOnHand = (int) $item->quantity_on_hand;
            if ($type === InventoryMovement::TYPE_RECEIVE || $type === InventoryMovement::TYPE_RETURN) {
                $newOnHand += $qty;
            } elseif ($type === InventoryMovement::TYPE_ISSUE) {
                if ($newOnHand - $qty < 0) {
                    abort(422, 'Quantity issued exceeds stock on hand.');
                }
                $newOnHand -= $qty;
            } elseif ($type === InventoryMovement::TYPE_ADJUST) {
                // For adjustments we treat `quantity` as the new absolute count.
                $newOnHand = $qty;
            }

            $item->quantity_on_hand = $newOnHand;
            $item->save();

            InventoryMovement::query()->create([
                'tenant_id' => (int) $request->user()->tenant_id,
                'inventory_item_id' => $item->id,
                'movement_type' => $type,
                'quantity' => $qty,
                'unit_cost' => $data['unit_cost'] ?? $item->unit_cost,
                'reason' => $data['reason'] ?? null,
                'performed_by_user_id' => $request->user()->id,
                'related_student_id' => $data['related_student_id'] ?? null,
                'related_staff_id' => $data['related_staff_id'] ?? null,
            ]);
        });

        return redirect()->route('inventory.movements')->with('status', __('Movement recorded.'));
    }
}
