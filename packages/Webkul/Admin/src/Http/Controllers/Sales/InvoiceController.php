<?php

namespace Webkul\Admin\Http\Controllers\Sales;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;
use Webkul\Admin\DataGrids\Sales\OrderInvoiceDataGrid;
use Webkul\Admin\Helpers\ElectronicInvoice;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Requests\MassUpdateRequest;
use Webkul\Core\Traits\PDFHandler;
use Webkul\Sales\Repositories\InvoiceRepository;
use Webkul\Sales\Repositories\OrderRepository;

class InvoiceController extends Controller
{
    use PDFHandler;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected OrderRepository $orderRepository,
        protected InvoiceRepository $invoiceRepository,
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(OrderInvoiceDataGrid::class)->process();
        }

        return view('admin::sales.invoices.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(int $orderId)
    {
        $order = $this->orderRepository->findOrFail($orderId);

        if ($order->payment->method === 'paypal_standard') {
            abort(404);
        }

        return view('admin::sales.invoices.create', compact('order'));
    }

    /**
     * (Store) a newly created resource in storage.
     *
     * @return Response
     */
    public function store(int $orderId)
    {
        $order = $this->orderRepository->findOrFail($orderId);

        if (! $order->canInvoice()) {
            session()->flash('error', trans('admin::app.sales.invoices.create.creation-error'));

            return redirect()->back();
        }

        $this->validate(request(), [
            'invoice.items' => 'required|array',
            'invoice.items.*' => 'required|numeric|min:0',
        ]);

        if (! $this->invoiceRepository->haveProductToInvoice(request()->all())) {
            session()->flash('error', trans('admin::app.sales.invoices.create.product-error'));

            return redirect()->back();
        }

        if (! $this->invoiceRepository->isValidQuantity(request()->all())) {
            session()->flash('error', trans('admin::app.sales.invoices.create.invalid-qty'));

            return redirect()->back();
        }

        $this->invoiceRepository->create(array_merge(request()->all(), [
            'order_id' => $orderId,
        ]));

        session()->flash('success', trans('admin::app.sales.invoices.create.create-success'));

        return redirect()->route('admin.sales.orders.view', $orderId);
    }

    /**
     * Show the view for the specified resource.
     *
     * @return View
     */
    public function view(int $id)
    {
        $invoice = $this->invoiceRepository->findOrFail($id);

        return view('admin::sales.invoices.view', compact('invoice'));
    }

    /**
     * Send duplicate invoice.
     *
     * @return Response
     */
    public function sendDuplicateEmail(Request $request, int $id)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $invoice = $this->invoiceRepository->findOrFail($id);

        Event::dispatch('sales.invoice.send_duplicate_email', [
            'invoice' => $invoice,
            'duplicate_invoice_email' => request()->input('email'),
        ]);

        session()->flash('success', trans('admin::app.sales.invoices.view.invoice-sent'));

        return redirect()->route('admin.sales.invoices.view', $invoice->id);
    }

    /**
     * Print and download the for the specified resource.
     *
     * @return Response
     */
    public function printInvoice(int $id)
    {
        $invoice = $this->invoiceRepository->findOrFail($id);

        $orderCurrencyCode = $invoice->order->order_currency_code;

        return $this->downloadPDF(
            view('shop::customers.account.orders.pdf', compact('invoice', 'orderCurrencyCode'))->render(),
            'invoice-'.$invoice->created_at->format('d-m-Y')
        );
    }

    /**
     * Generate a compact multi-copy HTML invoice sheet for printing.
     */
    public function compactInvoice(Request $request): Response
    {
        $request->validate([
            'indices'   => ['required', 'array'],
            'indices.*' => ['integer'],
        ]);

        $invoices = $this->invoiceRepository
            ->findWhereIn('id', $request->input('indices'));

        $filename = 'invoices_' . now()->format('Ymd_His') . '.html';

        return response(
            view('admin::sales.invoices.compact', compact('invoices'))->render()
        )
            ->header('Content-Type', 'text/html; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('X-Filename', $filename);
    }

    /**
     * Generate FatturaElettronica XML zip for the selected invoices.
     */
    public function electronicInvoice(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $request->validate([
            'indices'   => ['required', 'array'],
            'indices.*' => ['integer'],
        ]);

        $invoices = $this->invoiceRepository->findWhereIn('id', $request->input('indices'));

        $seq = (int) $request->input('prompt_value', 1);

        $zip = new \ZipArchive();
        $tmpPath = tempnam(sys_get_temp_dir(), 'fatture_') . '.zip';
        $zip->open($tmpPath, \ZipArchive::CREATE);

        foreach ($invoices as $invoice) {
            $zip->addFromString(
                ElectronicInvoice::filename($invoice, $seq),
                ElectronicInvoice::generate($invoice, $seq),
            );
            $seq++;
        }

        $zip->close();

        $filename = 'fatture_' . now()->format('Ymd_His') . '.zip';

        return response()->download($tmpPath, $filename, [
            'Content-Type' => 'application/zip',
            'X-Filename'   => $filename,
        ])->deleteFileAfterSend(true);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function massUpdateState(MassUpdateRequest $massUpdateRequest)
    {
        $invoiceIds = $massUpdateRequest->input('indices');

        $invoices = $this->invoiceRepository->findWhereIn('id', $invoiceIds);

        foreach ($invoices as $invoice) {
            $invoice->state = $massUpdateRequest->input('value');

            $invoice->save();
        }

        return new JsonResponse([
            'message' => trans('admin::app.sales.invoices.index.datagrid.mass-update-success'),
        ], 200);
    }
}
