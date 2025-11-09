<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\SalesOrder;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['salesOrder.customer', 'creator']);

        // Search by invoice number or customer name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('salesOrder.customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by customer
        if ($request->filled('customer_id')) {
            $query->whereHas('salesOrder', function ($q) use ($request) {
                $q->where('customer_id', $request->customer_id);
            });
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('invoice_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('invoice_date', '<=', $request->end_date);
        }

        // Filter by due date range
        if ($request->filled('due_start_date')) {
            $query->whereDate('due_date', '>=', $request->due_start_date);
        }
        if ($request->filled('due_end_date')) {
            $query->whereDate('due_date', '<=', $request->due_end_date);
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(10);

        // Get customers for filter dropdown
        $customers = Customer::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('invoices.index', compact('invoices', 'customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Get sales order if provided
        $salesOrder = null;
        if ($request->filled('sales_order_id')) {
            $salesOrder = SalesOrder::with(['customer', 'items.product'])
                ->findOrFail($request->sales_order_id);

            // Check if sales order is delivered
            if ($salesOrder->status !== 'delivered') {
                return redirect()->route('sales-orders.show', $salesOrder)
                    ->with('error', 'Hanya pesanan dengan status "Terkirim" yang dapat dibuatkan faktur.');
            }

            // Check if invoice already exists
            if ($salesOrder->invoice) {
                return redirect()->route('invoices.show', $salesOrder->invoice)
                    ->with('info', 'Faktur sudah dibuat untuk pesanan ini.');
            }
        }

        // Get delivered sales orders without invoices
        $salesOrders = SalesOrder::with('customer')
            ->where('status', 'delivered')
            ->doesntHave('invoice')
            ->orderBy('order_date', 'desc')
            ->get();

        return view('invoices.create', compact('salesOrder', 'salesOrders'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sales_order_id' => 'required|exists:sales_orders,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Get sales order
            $salesOrder = SalesOrder::findOrFail($validated['sales_order_id']);

            // Check if sales order is delivered
            if ($salesOrder->status !== 'delivered') {
                return back()->with('error', 'Hanya pesanan dengan status "Terkirim" yang dapat dibuatkan faktur.')
                    ->withInput();
            }

            // Check if invoice already exists
            if ($salesOrder->invoice) {
                return redirect()->route('invoices.show', $salesOrder->invoice)
                    ->with('info', 'Faktur sudah dibuat untuk pesanan ini.');
            }

            // Create invoice
            $invoice = new Invoice();
            $invoice->invoice_number = $invoice->generateInvoiceNumber();
            $invoice->sales_order_id = $salesOrder->id;
            $invoice->invoice_date = $validated['invoice_date'];
            $invoice->due_date = $validated['due_date'];
            $invoice->total_amount = $salesOrder->total;
            $invoice->paid_amount = 0.00;
            $invoice->payment_status = 'unpaid';
            $invoice->notes = $validated['notes'];
            $invoice->created_by = Auth::id();
            $invoice->updated_by = Auth::id();
            $invoice->save();

            // Update sales order payment status
            $salesOrder->payment_status = 'unpaid';
            $salesOrder->save();

            DB::commit();

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Faktur berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membuat faktur: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load([
            'salesOrder.customer',
            'salesOrder.warehouse',
            'salesOrder.items.product',
            'creator',
            'updater'
        ]);

        return view('invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        // Only allow editing unpaid invoices
        if ($invoice->payment_status !== 'unpaid') {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Hanya faktur yang belum dibayar yang dapat diedit.');
        }

        return view('invoices.edit', compact('invoice'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        // Only allow editing unpaid invoices
        if ($invoice->payment_status !== 'unpaid') {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Hanya faktur yang belum dibayar yang dapat diedit.');
        }

        $validated = $request->validate([
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'notes' => 'nullable|string',
        ]);

        try {
            $invoice->invoice_date = $validated['invoice_date'];
            $invoice->due_date = $validated['due_date'];
            $invoice->notes = $validated['notes'];
            $invoice->updated_by = Auth::id();
            $invoice->save();

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Faktur berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui faktur: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        // Only allow deleting unpaid invoices
        if ($invoice->payment_status !== 'unpaid') {
            return redirect()->route('invoices.index')
                ->with('error', 'Hanya faktur yang belum dibayar yang dapat dihapus.');
        }

        try {
            DB::beginTransaction();

            // Update sales order payment status
            $salesOrder = $invoice->salesOrder;
            $salesOrder->payment_status = 'unpaid';
            $salesOrder->save();

            $invoice->delete();

            DB::commit();

            return redirect()->route('invoices.index')
                ->with('success', 'Faktur berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus faktur: ' . $e->getMessage());
        }
    }

    /**
     * Record a payment for the invoice.
     */
    public function recordPayment(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,transfer,check,other',
            'payment_notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $remainingAmount = $invoice->total_amount - $invoice->paid_amount;

            // Validate payment amount
            if ($validated['amount'] > $remainingAmount) {
                return back()->with('error', 'Jumlah pembayaran melebihi sisa tagihan (Rp ' . number_format($remainingAmount, 0, ',', '.') . ').')
                    ->withInput();
            }

            // Update paid amount
            $invoice->paid_amount += $validated['amount'];

            // Update payment status
            if ($invoice->paid_amount >= $invoice->total_amount) {
                $invoice->payment_status = 'paid';
            } elseif ($invoice->paid_amount > 0) {
                $invoice->payment_status = 'partial';
            }

            // Update payment details (last payment)
            $invoice->payment_date = $validated['payment_date'];
            $invoice->payment_method = $validated['payment_method'];

            // Append new payment notes to existing notes
            $newNote = date('Y-m-d') . ' - Rp ' . number_format($validated['amount'], 0, ',', '.') . ' via ' . $validated['payment_method'];
            if (!empty($validated['payment_notes'])) {
                $newNote .= ': ' . $validated['payment_notes'];
            }

            if (!empty($invoice->payment_notes)) {
                $invoice->payment_notes .= "\n" . $newNote;
            } else {
                $invoice->payment_notes = $newNote;
            }

            $invoice->updated_by = Auth::id();
            $invoice->save();

            // Update sales order payment status
            $salesOrder = $invoice->salesOrder;
            $salesOrder->payment_status = $invoice->payment_status;
            $salesOrder->save();

            DB::commit();

            $message = 'Pembayaran berhasil dicatat.';
            if ($invoice->payment_status === 'paid') {
                $message .= ' Faktur sudah lunas.';
            }

            return redirect()->route('invoices.show', $invoice)
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mencatat pembayaran: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Generate and download invoice PDF (Faktur Pajak).
     */
    public function downloadPdf(Invoice $invoice)
    {
        $invoice->load([
            'salesOrder.customer',
            'salesOrder.warehouse',
            'salesOrder.items.product'
        ]);

        $pdf = PDF::loadView('invoices.pdf', compact('invoice'));

        return $pdf->download('faktur-' . $invoice->invoice_number . '.pdf');
    }

    /**
     * View invoice PDF in browser.
     */
    public function viewPdf(Invoice $invoice)
    {
        $invoice->load([
            'salesOrder.customer',
            'salesOrder.warehouse',
            'salesOrder.items.product'
        ]);

        $pdf = PDF::loadView('invoices.pdf', compact('invoice'));

        return $pdf->stream('faktur-' . $invoice->invoice_number . '.pdf');
    }
}
