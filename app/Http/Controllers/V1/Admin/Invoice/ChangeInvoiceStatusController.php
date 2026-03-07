<?php

namespace App\Http\Controllers\V1\Admin\Invoice;

use App\Enums\InvoiceStatus;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;

class ChangeInvoiceStatusController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request, Invoice $invoice)
    {
        $this->authorize('send invoice', $invoice);

        if ($request->status === InvoiceStatus::Sent->value) {
            $invoice->status = InvoiceStatus::Sent;
            $invoice->sent = true;
            $invoice->save();
        } elseif ($request->status === InvoiceStatus::Completed->value) {
            $invoice->status = InvoiceStatus::Completed;
            $invoice->paid_status = InvoiceStatus::Paid;
            $invoice->due_amount = 0;
            $invoice->save();
        }

        return response()->json([
            'success' => true,
        ]);
    }
}
