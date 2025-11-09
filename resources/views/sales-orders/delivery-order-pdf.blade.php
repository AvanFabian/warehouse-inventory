<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Surat Jalan - {{ $salesOrder->so_number }}</title>
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
         border: 2px solid #000;
         padding: 15px;
         margin-bottom: 20px;
      }

      .header-top {
         display: table;
         width: 100%;
         margin-bottom: 10px;
      }

      .logo-section {
         display: table-cell;
         width: 30%;
         vertical-align: middle;
      }

      .company-section {
         display: table-cell;
         width: 70%;
         text-align: right;
         vertical-align: middle;
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

      .document-title {
         text-align: center;
         font-size: 18pt;
         font-weight: bold;
         margin: 20px 0;
         padding: 10px;
         background-color: #333;
         color: #fff;
         text-transform: uppercase;
      }

      .info-section {
         margin-bottom: 20px;
      }

      .info-row {
         display: table;
         width: 100%;
         margin-bottom: 20px;
      }

      .info-box {
         display: table-cell;
         width: 50%;
         padding: 15px;
         border: 1px solid #333;
         vertical-align: top;
      }

      .info-box.left {
         border-right: none;
      }

      .info-box-title {
         font-weight: bold;
         font-size: 11pt;
         margin-bottom: 10px;
         padding-bottom: 5px;
         border-bottom: 1px solid #999;
      }

      .info-line {
         display: table;
         width: 100%;
         margin-bottom: 5px;
      }

      .info-label {
         display: table-cell;
         width: 40%;
         font-size: 9pt;
      }

      .info-value {
         display: table-cell;
         font-weight: bold;
      }

      table.items-table {
         width: 100%;
         border-collapse: collapse;
         margin: 20px 0;
      }

      table.items-table th {
         background-color: #333;
         color: #fff;
         padding: 10px 8px;
         text-align: left;
         font-weight: bold;
         border: 1px solid #000;
      }

      table.items-table th.center {
         text-align: center;
      }

      table.items-table th.right {
         text-align: right;
      }

      table.items-table td {
         padding: 8px;
         border: 1px solid #666;
      }

      table.items-table td.center {
         text-align: center;
      }

      table.items-table td.right {
         text-align: right;
      }

      table.items-table tbody tr:nth-child(even) {
         background-color: #f5f5f5;
      }

      .notes-section {
         margin: 20px 0;
         padding: 15px;
         border: 1px solid #999;
         background-color: #fafafa;
      }

      .notes-title {
         font-weight: bold;
         margin-bottom: 8px;
      }

      .notes-content {
         font-size: 9pt;
         color: #555;
      }

      .footer {
         margin-top: 40px;
         page-break-inside: avoid;
      }

      .signature-section {
         display: table;
         width: 100%;
      }

      .signature-box {
         display: table-cell;
         width: 33.33%;
         text-align: center;
         padding: 10px;
         border: 1px solid #999;
      }

      .signature-title {
         font-weight: bold;
         margin-bottom: 70px;
         font-size: 10pt;
      }

      .signature-line {
         border-top: 1px solid #000;
         padding-top: 5px;
         font-weight: bold;
         min-height: 20px;
      }

      .signature-date {
         font-size: 8pt;
         color: #666;
         margin-top: 3px;
      }

      .status-badge {
         display: inline-block;
         padding: 5px 15px;
         border-radius: 3px;
         font-size: 10pt;
         font-weight: bold;
      }

      .status-confirmed {
         background-color: #e3f2fd;
         color: #1976d2;
         border: 1px solid #1976d2;
      }

      .status-shipped {
         background-color: #fff9c4;
         color: #f57c00;
         border: 1px solid #f57c00;
      }

      .status-delivered {
         background-color: #e8f5e9;
         color: #388e3c;
         border: 1px solid #388e3c;
      }

      .important-note {
         background-color: #fff3cd;
         border: 2px solid #ffc107;
         padding: 10px;
         margin: 15px 0;
         font-size: 9pt;
      }

      .document-footer {
         margin-top: 30px;
         text-align: center;
         font-size: 8pt;
         color: #999;
         border-top: 1px solid #ddd;
         padding-top: 10px;
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
         <div class="header-top">
            <div class="logo-section">
               <!-- Logo placeholder -->
               <div
                  style="width: 80px; height: 80px; border: 2px solid #333; display: flex; align-items: center; justify-content: center; font-size: 8pt; color: #999;">
                  LOGO
               </div>
            </div>
            <div class="company-section">
               <div class="company-name">PT. NAMA PERUSAHAAN ANDA</div>
               <div class="company-details">
                  Alamat: Jl. Contoh No. 123, Jakarta 12345<br>
                  Telp: (021) 1234-5678 | Fax: (021) 1234-5679<br>
                  Email: info@perusahaan.com | Website: www.perusahaan.com<br>
                  NPWP: 01.234.567.8-901.000
               </div>
            </div>
         </div>
      </div>

      <!-- Document Title -->
      <div class="document-title">
         Surat Jalan / Delivery Order
      </div>

      <!-- Order Info & Customer Info -->
      <div class="info-row">
         <div class="info-box left">
            <div class="info-box-title">Informasi Pengiriman</div>
            <div class="info-line">
               <div class="info-label">No. Surat Jalan:</div>
               <div class="info-value">{{ $salesOrder->so_number }}</div>
            </div>
            <div class="info-line">
               <div class="info-label">Tanggal:</div>
               <div class="info-value">{{ $salesOrder->order_date->format('d F Y') }}</div>
            </div>
            <div class="info-line">
               <div class="info-label">Tanggal Kirim:</div>
               <div class="info-value">
                  {{ $salesOrder->delivery_date ? $salesOrder->delivery_date->format('d F Y') : '-' }}</div>
            </div>
            <div class="info-line">
               <div class="info-label">Gudang:</div>
               <div class="info-value">{{ $salesOrder->warehouse->name }}</div>
            </div>
            <div class="info-line">
               <div class="info-label">Status:</div>
               <div class="info-value">
                  @if ($salesOrder->status === 'confirmed')
                     <span class="status-badge status-confirmed">CONFIRMED</span>
                  @elseif($salesOrder->status === 'shipped')
                     <span class="status-badge status-shipped">DIKIRIM</span>
                  @elseif($salesOrder->status === 'delivered')
                     <span class="status-badge status-delivered">TERKIRIM</span>
                  @endif
               </div>
            </div>
         </div>
         <div class="info-box">
            <div class="info-box-title">Kepada Yth:</div>
            <div class="info-line">
               <div class="info-label">Nama:</div>
               <div class="info-value">{{ $salesOrder->customer->name }}</div>
            </div>
            <div class="info-line">
               <div class="info-label">Alamat:</div>
               <div class="info-value">{{ $salesOrder->customer->address ?? '-' }}</div>
            </div>
            <div class="info-line">
               <div class="info-label">Telp:</div>
               <div class="info-value">{{ $salesOrder->customer->phone ?? '-' }}</div>
            </div>
            <div class="info-line">
               <div class="info-label">Email:</div>
               <div class="info-value">{{ $salesOrder->customer->email ?? '-' }}</div>
            </div>
            @if ($salesOrder->customer->tax_id)
               <div class="info-line">
                  <div class="info-label">NPWP:</div>
                  <div class="info-value">{{ $salesOrder->customer->tax_id }}</div>
               </div>
            @endif
         </div>
      </div>

      <!-- Important Note -->
      <div class="important-note">
         <strong>⚠ PERHATIAN:</strong> Harap periksa barang yang diterima. Jika ada kerusakan atau kekurangan, segera
         laporkan kepada pengirim.
      </div>

      <!-- Items Table -->
      <table class="items-table">
         <thead>
            <tr>
               <th class="center" style="width: 5%;">No</th>
               <th style="width: 45%;">Nama Produk / Deskripsi</th>
               <th class="center" style="width: 15%;">Qty</th>
               <th class="center" style="width: 15%;">Satuan</th>
               <th style="width: 20%;">Keterangan</th>
            </tr>
         </thead>
         <tbody>
            @foreach ($salesOrder->items as $index => $item)
               <tr>
                  <td class="center">{{ $index + 1 }}</td>
                  <td>
                     <strong>{{ $item->product->name }}</strong><br>
                     <span style="font-size: 8pt; color: #666;">
                        SKU: {{ $item->product->sku }}<br>
                        Kategori: {{ $item->product->category->name ?? '-' }}
                     </span>
                  </td>
                  <td class="center" style="font-size: 12pt; font-weight: bold;">{{ number_format($item->quantity) }}
                  </td>
                  <td class="center">{{ $item->product->unit ?? 'PCS' }}</td>
                  <td style="font-size: 8pt; color: #666;">
                     @if ($item->product->description)
                        {{ Str::limit($item->product->description, 50) }}
                     @else
                        -
                     @endif
                  </td>
               </tr>
            @endforeach

            <!-- Summary Row -->
            <tr style="background-color: #e0e0e0; font-weight: bold;">
               <td colspan="2" class="right" style="padding: 10px;">TOTAL ITEM:</td>
               <td class="center" style="font-size: 12pt;">{{ number_format($salesOrder->items->sum('quantity')) }}
               </td>
               <td colspan="2"></td>
            </tr>
         </tbody>
      </table>

      <!-- Notes -->
      @if ($salesOrder->notes)
         <div class="notes-section">
            <div class="notes-title">Catatan Pengiriman:</div>
            <div class="notes-content">{{ $salesOrder->notes }}</div>
         </div>
      @endif

      <!-- Additional Info -->
      <div style="margin: 20px 0; padding: 10px; background-color: #f0f0f0; border-left: 4px solid #333;">
         <strong>Informasi Tambahan:</strong><br>
         <div style="font-size: 9pt; margin-top: 5px;">
            • Barang yang sudah diterima tidak dapat dikembalikan<br>
            • Harap simpan surat jalan ini sebagai bukti pengiriman<br>
            • Untuk pertanyaan, hubungi: (021) 1234-5678
         </div>
      </div>

      <!-- Signature Section -->
      <div class="footer">
         <div style="margin-bottom: 10px; text-align: center; font-weight: bold; font-size: 11pt;">
            TANDA TERIMA
         </div>

         <div class="signature-section">
            <div class="signature-box">
               <div class="signature-title">Pengirim</div>
               <div style="min-height: 80px;"></div>
               <div class="signature-line">
                  ( _________________ )
               </div>
               <div class="signature-date">Nama Jelas & Tanda Tangan</div>
            </div>
            <div class="signature-box">
               <div class="signature-title">Supir/Kurir</div>
               <div style="min-height: 80px;"></div>
               <div class="signature-line">
                  ( _________________ )
               </div>
               <div class="signature-date">Nama Jelas & Tanda Tangan</div>
            </div>
            <div class="signature-box">
               <div class="signature-title">Penerima</div>
               <div style="min-height: 80px;"></div>
               <div class="signature-line">
                  ( _________________ )
               </div>
               <div class="signature-date">
                  Nama Jelas, Tanda Tangan & Stempel<br>
                  Tanggal: ___ / ___ / ______
               </div>
            </div>
         </div>
      </div>

      <!-- Document Footer -->
      <div class="document-footer">
         Dokumen ini dicetak pada {{ now()->format('d F Y H:i:s') }}<br>
         Dibuat oleh: {{ $salesOrder->creator->name ?? 'System' }}<br>
         <em>Dokumen ini sah tanpa tanda tangan dan stempel</em>
      </div>
   </div>
</body>

</html>
