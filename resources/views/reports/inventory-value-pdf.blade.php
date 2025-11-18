<!DOCTYPE html>
<html>

<head>
   <meta charset="utf-8">
   <title>Inventory Value Report - {{ date('d M Y') }}</title>
   <style>
      body {
         font-family: Arial, sans-serif;
         font-size: 12px;
      }

      h1 {
         font-size: 18px;
         text-align: center;
      }

      .summary {
         margin: 20px 0;
         background-color: #f9fafb;
         padding: 15px;
         border-radius: 5px;
         text-align: center;
      }

      .summary .total {
         font-size: 24px;
         font-weight: bold;
         color: #2563eb;
      }

      table {
         width: 100%;
         border-collapse: collapse;
         margin-top: 20px;
      }

      th,
      td {
         border: 1px solid #ddd;
         padding: 8px;
         text-align: left;
      }

      th {
         background-color: #f3f4f6;
         font-weight: bold;
      }

      .text-right {
         text-align: right;
      }

      tfoot th {
         background-color: #f3f4f6;
         font-weight: bold;
      }
   </style>
</head>

<body>
   <h1>Inventory Value Report</h1>
   <p style="text-align: center;">Generated on {{ date('d F Y H:i') }}</p>

   <div class="summary">
      <p>Total Inventory Value</p>
      <p class="total">Rp {{ number_format($totalValue) }}</p>
      <p style="font-size: 10px; margin-top: 5px;">Based on purchase price</p>
   </div>

   <h3>Value by Category</h3>
   <table>
      <thead>
         <tr>
            <th>Category</th>
            <th class="text-right">Product Count</th>
            <th class="text-right">Total Value</th>
            <th class="text-right">Percentage</th>
         </tr>
      </thead>
      <tbody>
         @foreach ($categories as $cat)
            @php
               $percentage = $totalValue > 0 ? ($cat->total_value / $totalValue) * 100 : 0;
            @endphp
            <tr>
               <td>{{ $cat->name }}</td>
               <td class="text-right">{{ $cat->products_count }}</td>
               <td class="text-right">Rp {{ number_format($cat->total_value) }}</td>
               <td class="text-right">{{ number_format($percentage, 1) }}%</td>
            </tr>
         @endforeach
      </tbody>
   </table>

   <h3 style="margin-top: 30px;">Product Details</h3>
   <table>
      <thead>
         <tr>
            <th>Code</th>
            <th>Product</th>
            <th>Category</th>
            <th class="text-right">Stock</th>
            <th class="text-right">Purchase Price</th>
            <th class="text-right">Total Value</th>
         </tr>
      </thead>
      <tbody>
         @foreach ($allProducts as $p)
            <tr>
               <td>{{ $p->code }}</td>
               <td>{{ $p->name }}</td>
               <td>{{ $p->category?->name ?? '-' }}</td>
               @php
                  $totalStock = $p->warehouses->sum('pivot.stock');
               @endphp
               <td class="text-right">{{ $totalStock }} {{ $p->unit }}</td>
               <td class="text-right">Rp {{ number_format($p->purchase_price) }}</td>
               <td class="text-right">Rp {{ number_format($totalStock * $p->purchase_price) }}</td>
            </tr>
         @endforeach
      </tbody>
      <tfoot>
         <tr>
            <th colspan="5" class="text-right">GRAND TOTAL:</th>
            <th class="text-right">Rp {{ number_format($totalValue) }}</th>
         </tr>
      </tfoot>
   </table>
</body>

</html>
