<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\School;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class ReceiptController extends Controller
{
    public function pdf(Payment $payment): Response
    {
        $this->authorize('view', $payment);

        $payment->load(['student.schoolClass', 'invoice', 'receiver']);
        $school = School::query()->where('tenant_id', $payment->tenant_id)->first();

        $previousPaid = (float) Payment::query()
            ->where('tenant_id', $payment->tenant_id)
            ->where('student_id', $payment->student_id)
            ->where('status', Payment::STATUS_SUCCESSFUL)
            ->where(function ($q) use ($payment): void {
                $q->where('date', '<', $payment->date)
                    ->orWhere(function ($q) use ($payment): void {
                        $q->where('date', $payment->date)->where('id', '<=', $payment->id);
                    });
            })
            ->sum('amount');

        $pdf = Pdf::loadView('payments.receipt-pdf', [
            'payment' => $payment,
            'school' => $school,
            'cumulativePaid' => $previousPaid,
        ])->setPaper('a5');

        return $pdf->download('receipt-'.$payment->receipt_id.'.pdf');
    }
}
