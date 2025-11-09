<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Faktur Pajak - {{ $invoice->invoice_number }}</title>
   <style>
      * {
         margin: 0;
         padding: 0;
         box-sizing: border-box;
      }

      body {
         font-family: 'Arial', sans-serif;
         font-size: 10pt;
         line-height: 1.4;
         color: #000;
      }

      .container {
         padding: 20px;
         max-width: 210mm;
         margin: 0 auto;
      }

      .header {
         border-bottom: 3px solid #000;
         padding-bottom: 15px;
         margin-bottom: 20px;
      }

      .company-info {
         text-align: center;
         margin-bottom: 10px;
      }

      .company-name {
         font-size: 18pt;
         font-weight: bold;
         margin-bottom: 5px;
      }

      .company-details {
         font-size: 9pt;
         color: #333;
      }

      .invoice-title {
         text-align: center;
         font-size: 16pt;
         font-weight: bold;
         margin: 20px 0;
         text-transform: uppercase;
         background-color: #f0f0f0;
         padding: 10px;
         border: 2px solid #000;
      }

      .info-section {
         margin-bottom: 20px;
      }

      .info-grid {
         display: table;
         width: 100%;
         margin-bottom: 15px;
      }

      .info-row {
         display: table-row;
      }

      .info-label {
         display: table-cell;
         width: 30%;
         padding: 4px 0;
         font-weight: bold;
      }

      .info-value {
         display: table-cell;
         padding: 4px 0;
      }

      .section-title {
         font-weight: bold;
         font-size: 11pt;
         margin-bottom: 8px;
         padding-bottom: 3px;
         border-bottom: 2px solid #333;
      }

      table.items-table {
         width: 100%;
         border-collapse: collapse;
         margin: 15px 0;
      }

      table.items-table th {
         background-color: #333;
         color: #fff;
         padding: 8px;
         text-align: left;
         font-weight: bold;
         border: 1px solid #000;
      }

      table.items-table th.right {
         text-align: right;
      }

      table.items-table th.center {
         text-align: center;
      }

      table.items-table td {
         padding: 6px 8px;
         border: 1px solid #999;
      }

      table.items-table td.right {
         text-align: right;
      }

      table.items-table td.center {
         text-align: center;
      }

      table.items-table tbody tr:nth-child(even) {
         background-color: #f9f9f9;
      }

      .totals-section {
         float: right;
         width: 50%;
         margin-top: 10px;
      }

      .totals-table {
         width: 100%;
         border-collapse: collapse;
      }

      .totals-table td {
         padding: 6px 10px;
      }

      .totals-table td.label {
         text-align: right;
         font-weight: bold;
         width: 50%;
      }

      .totals-table td.value {
         text-align: right;
         border-bottom: 1px solid #ddd;
      }

      .totals-table tr.total td {
         font-size: 12pt;
         font-weight: bold;
         border-top: 2px solid #000;
         border-bottom: 3px double #000;
         padding: 10px;
      }

      .totals-table tr.total td.value {
         background-color: #f0f0f0;
      }

      .notes-section {
         clear: both;
         margin-top: 30px;
         padding-top: 15px;
         border-top: 1px solid #ddd;
      }

      .notes-title {
         font-weight: bold;
         margin-bottom: 5px;
      }

      .notes-content {
         font-size: 9pt;
         color: #555;
      }

      .footer {
         margin-top: 50px;
         page-break-inside: avoid;
      }

      .signature-section {
         display: table;
         width: 100%;
      }

      .signature-box {
         display: table-cell;
         width: 33%;
         text-align: center;
         padding: 10px;
      }

      .signature-title {
         font-weight: bold;
         margin-bottom: 60px;
      }

      .signature-line {
         border-top: 1px solid #000;
         padding-top: 5px;
         font-weight: bold;
      }

      .tax-info {
         background-color: #ffe;
         border: 1px solid #dd9;
         padding: 10px;
         margin: 15px 0;
         font-size: 9pt;
      }

      .status-badge {
         display: inline-block;
         padding: 4px 12px;
         border-radius: 4px;
         font-size: 9pt;
         font-weight: bold;
      }

      .status-unpaid {
         background-color: #fee;
         color: #c00;
         border: 1px solid #c00;
      }

      .status-partial {
         background-color: #ffc;
         color: #880;
         border: 1px solid #880;
      }

      .status-paid {
         background-color: #efe;
         color: #080;
         border: 1px solid #080;
      }

      .payment-info {
         background-color: #e8f4f8;
         border: 1px solid #4a90a4;
         padding: 10px;
         margin: 15px 0;
      }

      @media print {
         body {
            margin: 0;
            padding: 0;
         }

         .container {
            padding: 0;
         }
      }
   </style>
</head>

<body>
   <div class="container">
      <!-- Header -->
      <div class="header">
         <div class="company-info">
            <div class="company-name">PT. NAMA PERUSAHAAN ANDA</div>
            <div class="company-details">
               Alamat: Jl. Contoh No. 123, Jakarta 12345<br>
               Telp: (021) 1234-5678 | Email: info@perusahaan.com<br>
               NPWP: 01.234.567.8-901.000
            </div>
         </div>
      </div>

      <!-- Invoice Title -->
      <div class="invoice-title">
         Faktur Pajak
      </div>

      <!-- Invoice Info -->
      <div class="info-section">
         <div class="info-grid">
            <div class="info-row">
               <div class="info-label">No. Faktur:</div>
               <div class="info-value">{{ $invoice->invoice_number }}</div>
               <div class="info-label" style="text-align: right;">Tanggal:</div>
               <div class="info-value" style="text-align: right;">{{ $invoice->invoice_date->format('d F Y') }}</div>
            </div>
            <div class="info-row">
               <div class="info-label">No. Pesanan:</div>
               <div class="info-value">{{ $invoice->salesOrder->so_number }}</div>
               <div class="info-label" style="text-align: right;">Jatuh Tempo:</div>
               <div class="info-value" style="text-align: right;">
                  {{ $invoice->due_date->format('d F Y') }}
                  @if ($invoice->due_date->isPast() && $invoice->payment_status !== 'paid')
                     <span style="color: #c00; font-weight: bold;">(TERLAMBAT)</span>
                  @endif
               </div>
            </div>
         </div>
      </div>

      <!-- Customer Info -->
      <div class="info-section">
         <div class="section-title">Kepada Yth:</div>
         <div class="info-grid">
            <div class="info-row">
               <div class="info-label">Nama Pelanggan:</div>
               <div class="info-value">{{ $invoice->salesOrder->customer->name }}</div>
            </div>
            <div class="info-row">
               <div class="info-label">Alamat:</div>
               <div class="info-value">{{ $invoice->salesOrder->customer->address ?? '-' }}</div>
            </div>
            <div class="info-row">
               <div class="info-label">Telp:</div>
               <div class="info-value">{{ $invoice->salesOrder->customer->phone ?? '-' }}</div>
            </div>
            @if ($invoice->salesOrder->customer->tax_id)
               <div class="info-row">
                  <div class="info-label">NPWP:</div>
                  <div class="info-value">{{ $invoice->salesOrder->customer->tax_id }}</div>
               </div>
            @endif
         </div>
      </div>

      <!-- Payment Status -->
      <div style="margin: 15px 0;">
         <strong>Status Pembayaran:</strong>
         @if ($invoice->payment_status === 'unpaid')
            <span class="status-badge status-unpaid">BELUM DIBAYAR</span>
         @elseif($invoice->payment_status === 'partial')
            <span class="status-badge status-partial">DIBAYAR SEBAGIAN</span>
         @else
            <span class="status-badge status-paid">LUNAS</span>
         @endif
      </div>

      @if ($invoice->payment_status !== 'unpaid')
         <div class="payment-info">
            <strong>Informasi Pembayaran:</strong><br>
            Terbayar: <strong>Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</strong><br>
            @if ($invoice->payment_status === 'partial')
               Sisa: <strong style="color: #c00;">Rp
                  {{ number_format($invoice->remaining_amount, 0, ',', '.') }}</strong><br>
            @endif
            @if ($invoice->payment_date)
               Tanggal Pembayaran Terakhir: {{ $invoice->payment_date->format('d F Y') }}<br>
            @endif
            @if ($invoice->payment_method)
               Metode: {{ ucfirst($invoice->payment_method) }}
            @endif
         </div>
      @endif

      <!-- Items Table -->
      <table class="items-table">
         <thead>
            <tr>
               <th class="center" style="width: 5%;">No</th>
               <th style="width: 45%;">Nama Produk</th>
               <th class="center" style="width: 10%;">Qty</th>
               <th class="right" style="width: 18%;">Harga Satuan</th>
               <th class="right" style="width: 22%;">Subtotal</th>
            </tr>
         </thead>
         <tbody>
            @foreach ($invoice->salesOrder->items as $index => $item)
               <tr>
                  <td class="center">{{ $index + 1 }}</td>
                  <td>
                     <strong>{{ $item->product->name }}</strong><br>
                     <span style="font-size: 8pt; color: #666;">SKU: {{ $item->product->sku }}</span>
                  </td>
                  <td class="center">{{ number_format($item->quantity) }}</td>
                  <td class="right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                  <td class="right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
               </tr>
            @endforeach
         </tbody>
      </table>

      <!-- Totals -->
      <div class="totals-section">
         <table class="totals-table">
            <tr>
               <td class="label">Subtotal:</td>
               <td class="value">Rp {{ number_format($invoice->salesOrder->subtotal, 0, ',', '.') }}</td>
            </tr>
            @if ($invoice->salesOrder->discount > 0)
               <tr>
                  <td class="label">Diskon:</td>
                  <td class="value" style="color: #c00;">- Rp
                     {{ number_format($invoice->salesOrder->discount, 0, ',', '.') }}</td>
               </tr>
            @endif
            <tr>
               <td class="label">PPN 11%:</td>
               <td class="value">Rp {{ number_format($invoice->salesOrder->tax, 0, ',', '.') }}</td>
            </tr>
            <tr class="total">
               <td class="label">TOTAL:</td>
               <td class="value">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
            </tr>
         </table>
      </div>

      <!-- Tax Info -->
      <div class="tax-info" style="clear: both;">
         <strong>Keterangan Pajak:</strong><br>
         Faktur ini merupakan bukti pungutan pajak yang sah sesuai dengan Undang-Undang PPN.<br>
         PPN 11% telah dipungut sesuai ketentuan perpajakan yang berlaku.
      </div>

      <!-- Notes -->
      @if ($invoice->notes)
         <div class="notes-section">
            <div class="notes-title">Catatan:</div>
            <div class="notes-content">{{ $invoice->notes }}</div>
         </div>
      @endif

      <!-- Footer / Signature -->
      <div class="footer">
         <div style="font-size: 9pt; margin-bottom: 20px;">
            <strong>Informasi Pembayaran:</strong><br>
            Bank: BCA / Mandiri / BNI<br>
            No. Rekening: 1234567890<br>
            A/n: PT. Nama Perusahaan Anda
         </div>

         <div class="signature-section">
            <div class="signature-box">
               <div class="signature-title">Hormat Kami,</div>
               <div class="signature-line">
                  (&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)
               </div>
            </div>
            <div class="signature-box">
               <div class="signature-title">Mengetahui,</div>
               <div class="signature-line">
                  (&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)
               </div>
            </div>
            <div class="signature-box">
               <div class="signature-title">Penerima,</div>
               <div class="signature-line">
                  (&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)
               </div>
            </div>
         </div>
      </div>

      <!-- Document Info -->
      <div
         style="margin-top: 30px; text-align: center; font-size: 8pt; color: #999; border-top: 1px solid #ddd; padding-top: 10px;">
         Dokumen ini dicetak pada {{ now()->format('d F Y H:i:s') }}<br>
         Dibuat oleh: {{ $invoice->creator->name ?? 'System' }}
      </div>
   </div>
</body>

</html>
