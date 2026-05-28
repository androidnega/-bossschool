<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FeeInvoice;
use App\Models\PaymentTransaction;
use App\Services\Payments\PaymentGatewayService;
use App\Services\Payments\PaymentProviderRegistry;
use App\Services\TenantSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Surfaces online payment activity in the UI:
 *  - GET  /payment-transactions               staff index
 *  - POST /fee-invoices/{invoice}/pay-online  parent/student/staff initiates an online payment
 */
class PaymentTransactionController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', PaymentTransaction::class);

        $transactions = PaymentTransaction::query()
            ->with(['student.schoolClass', 'invoice', 'initiator'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('provider'), fn ($q) => $q->where('provider', $request->string('provider')))
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('payment-transactions.index', [
            'transactions' => $transactions,
            'statuses' => PaymentTransaction::STATUSES,
        ]);
    }

    /**
     * Initiate an online payment for a specific invoice. We don't actually
     * call upstream APIs here — we record an `initiated` row that a future
     * UI / gateway integration can drive to completion via the webhook flow.
     */
    public function initiate(
        Request $request,
        FeeInvoice $feeInvoice,
        PaymentGatewayService $gateway,
        PaymentProviderRegistry $registry,
        TenantSettings $settings,
    ): RedirectResponse {
        $this->authorize('initiate', [PaymentTransaction::class, $feeInvoice]);

        $user = $request->user();
        if ($user && $user->role === \App\Enums\UserRole::Parent->value) {
            $enabled = (bool) $settings->get((int) $feeInvoice->tenant_id, 'parent_online_payment_enabled', false);
            if (! $enabled) {
                abort(403, 'Online payment is disabled for parents at this school.');
            }
        }
        if ($user && $user->role === \App\Enums\UserRole::Student->value) {
            $enabled = (bool) $settings->get((int) $feeInvoice->tenant_id, 'student_online_payment_enabled', false);
            if (! $enabled) {
                abort(403, 'Online payment is disabled for students at this school.');
            }
        }

        $providerKey = (string) $request->input('provider', '');
        $enabledKeys = $registry->enabledKeys();
        if (! in_array($providerKey, $enabledKeys, true)) {
            return back()->withErrors(['provider' => __('Choose a configured payment provider.')]);
        }

        $amount = $request->filled('amount') ? (float) $request->input('amount') : null;

        $transaction = $gateway->initiate(
            initiator: $user,
            invoice: $feeInvoice,
            providerKey: $providerKey,
            amount: $amount,
            checkoutUrl: null,
            providerReference: null,
            rawRequest: ['ip' => $request->ip(), 'user_id' => $user->id],
        );

        return redirect()
            ->route('fee-invoices.show', $feeInvoice)
            ->with('status', __('Payment initiated. Reference: :ref', ['ref' => $transaction->provider_reference]));
    }
}
