<!DOCTYPE html>
<html>

<head>
   <meta charset="utf-8">
   <title>Product Labels</title>
   <style>
      @page {
         margin: 10mm;
      }

      body {
         font-family: Arial, sans-serif;
         margin: 0;
         padding: 0;
      }

      .label-container {
         display: inline-block;
         width: 90mm;
         height: 50mm;
         border: 1px solid #ddd;
         padding: 5mm;
         margin: 2mm;
         page-break-inside: avoid;
         vertical-align: top;
      }

      .product-name {
         font-size: 12pt;
         font-weight: bold;
         margin-bottom: 2mm;
         text-align: center;
      }

      .product-code {
         font-size: 10pt;
         text-align: center;
         margin-bottom: 2mm;
      }

      .barcode-section {
         text-align: center;
         margin: 3mm 0;
      }

      .barcode-section img {
         height: 40px;
      }

      .qr-section {
         text-align: center;
         margin: 3mm 0;
      }

      .qr-section img {
         width: 80px;
         height: 80px;
      }

      .product-info {
         font-size: 8pt;
         margin-top: 2mm;
      }

      .info-row {
         margin-bottom: 1mm;
      }

      .label-text {
         font-weight: bold;
      }

      .flex-container {
         display: table;
         width: 100%;
      }

      .flex-left {
         display: table-cell;
         width: 60%;
         vertical-align: top;
      }

      .flex-right {
         display: table-cell;
         width: 40%;
         text-align: center;
         vertical-align: top;
      }
   </style>
</head>

<body>
   @foreach ($products as $product)
      <div class="label-container">
         <div class="product-name">{{ $product->name }}</div>
         <div class="product-code">{{ $product->code }}</div>

         <div class="flex-container">
            <div class="flex-left">
               <div class="barcode-section">
                  <img
                     src="data:image/png;base64,{{ base64_encode(new \Picqer\Barcode\BarcodeGeneratorPNG()->getBarcode($product->code, \Picqer\Barcode\BarcodeGeneratorPNG::TYPE_CODE_128)) }}"
                     alt="Barcode">
               </div>

               <div class="product-info">
                  <div class="info-row">
                     <span class="label-text">Category:</span> {{ $product->category->name ?? '-' }}
                  </div>
                  <div class="info-row">
                     <span class="label-text">Warehouse:</span> {{ $product->warehouse->name ?? '-' }}
                  </div>
                  <div class="info-row">
                     <span class="label-text">Price:</span> Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                  </div>
                  <div class="info-row">
                     <span class="label-text">Stock:</span> {{ $product->stock }} {{ $product->unit }}
                  </div>
               </div>
            </div>

            <div class="flex-right">
               <div class="qr-section">
                  {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(200)->generate(
                      json_encode([
                          'id' => $product->id,
                          'code' => $product->code,
                          'name' => $product->name,
                          'price' => $product->selling_price,
                      ]),
                  ) !!}
               </div>
            </div>
         </div>
      </div>
   @endforeach
</body>

</html>
