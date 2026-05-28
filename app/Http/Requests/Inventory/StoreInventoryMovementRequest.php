<?php

namespace App\Http\Requests\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInventoryMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        $item = InventoryItem::query()->find($this->input('inventory_item_id'));
        if (! $item) {
            return false;
        }

        return $this->user()?->can('move', $item) ?? false;
    }

    public function rules(): array
    {
        return [
            'inventory_item_id' => ['required', 'integer', 'exists:inventory_items,id'],
            'movement_type' => ['required', 'string', Rule::in(InventoryMovement::TYPES)],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'reason' => ['nullable', 'string', 'max:255'],
            'related_student_id' => ['nullable', 'integer', 'exists:students,id'],
            'related_staff_id' => ['nullable', 'integer', 'exists:staff,id'],
        ];
    }
}
