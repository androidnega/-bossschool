<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreFeeInvoiceItemRequest;
use App\Models\FeeInvoice;
use App\Models\FeeInvoiceItem;
use App\Services\InvoiceCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FeeInvoiceItemController extends Controller
{
    public function __construct(private readonly InvoiceCalculator $calculator) {}

    public function store(StoreFeeInvoiceItemRequest $request, FeeInvoice $feeInvoice): RedirectResponse
    {
        $feeInvoice->items()->create([
            'tenant_id' => (int) $feeInvoice->tenant_id,
            'fee_id' => $request->input('fee_id'),
            'description' => (string) $request->input('description'),
            'category' => $request->input('category'),
            'quantity' => max(1, (int) $request->input('quantity', 1)),
            'unit_amount' => (float) $request->input('unit_amount'),
        ]);

        $this->calculator->refresh($feeInvoice);

        return back()->with('status', __('Item added.'));
    }

    public function update(StoreFeeInvoiceItemRequest $request, FeeInvoice $feeInvoice, FeeInvoiceItem $item): RedirectResponse
    {
        abort_unless((int) $item->fee_invoice_id === (int) $feeInvoice->id, 404);

        $item->update([
            'fee_id' => $request->input('fee_id'),
            'description' => (string) $request->input('description'),
            'category' => $request->input('category'),
            'quantity' => max(1, (int) $request->input('quantity', 1)),
            'unit_amount' => (float) $request->input('unit_amount'),
        ]);

        $this->calculator->refresh($feeInvoice);

        return back()->with('status', __('Item updated.'));
    }

    public function destroy(Request $request, FeeInvoice $feeInvoice, FeeInvoiceItem $item): RedirectResponse
    {
        abort_unless((int) $item->fee_invoice_id === (int) $feeInvoice->id, 404);

        $this->authorizeForUser($request->user(), 'update', $feeInvoice);

        $item->delete();
        $this->calculator->refresh($feeInvoice);

        return back()->with('status', __('Item removed.'));
    }
}
