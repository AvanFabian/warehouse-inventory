<!DOCTYPE html>
<html>

<head>
   <meta charset="utf-8">
   <title>Stock Card - {{ $product->name }}</title>
   <style>
      body {
         font-family: Arial, sans-serif;
         font-size: 12px;
      }

      h1 {
         font-size: 18px;
         text-align: center;
      }

      .product-info {
         margin: 20px 0;
         background-color: #f9fafb;
         padding: 10px;
         border-radius: 5px;
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

      .text-center {
         text-align: center;
      }

      .bg-gray {
         background-color: #f9fafb;
         font-weight: bold;
      }
   </style>
</head>

<body>
   <h1>Stock Card Report</h1>
   <p style="text-align: center;">
      @if (request('from') && request('to'))
         Period: {{ \Carbon\Carbon::parse(request('from'))->format('d M Y') }} -
         {{ \Carbon\Carbon::parse(request('to'))->format('d M Y') }}
      @else
         All Periods
      @endif
   </p>
   <p style="text-align: center;">Generated on {{ date('d F Y H:i') }}</p>

   <div class="product-info">
      <strong>Product:</strong> {{ $product->code }} - {{ $product->name }}<br>
      <strong>Current Stock:</strong> {{ $product->stock }} {{ $product->unit }}
   </div>

   <table>
      <thead>
         <tr>
            <th>Date</th>
            <th>Transaction Code</th>
            <th>Type</th>
            <th class="text-right">In</th>
            <th class="text-right">Out</th>
            <th class="text-right">Balance</th>
         </tr>
      </thead>
      <tbody>
         <!-- Beginning Balance -->
         <tr class="bg-gray">
            <td>{{ request('from') ? \Carbon\Carbon::parse(request('from'))->format('d M Y') : '-' }}</td>
            <td colspan="2">Beginning Balance</td>
            <td class="text-right">-</td>
            <td class="text-right">-</td>
            <td class="text-right">{{ $beginningBalance }}</td>
         </tr>

         @php $runningBalance = $beginningBalance; @endphp
         @foreach ($movements as $move)
            @php
               $runningBalance += $move->type === 'in' ? $move->quantity : -$move->quantity;
            @endphp
            <tr>
               <td>{{ $move->date->format('d M Y') }}</td>
               <td>{{ $move->transaction_code }}</td>
               <td>{{ ucfirst($move->type) }}</td>
               <td class="text-right">{{ $move->type === 'in' ? $move->quantity : '-' }}</td>
               <td class="text-right">{{ $move->type === 'out' ? $move->quantity : '-' }}</td>
               <td class="text-right">{{ $runningBalance }}</td>
            </tr>
         @endforeach

         <!-- Ending Balance -->
         <tr class="bg-gray">
            <td colspan="3">Ending Balance</td>
            <td class="text-right">{{ $totalIn }}</td>
            <td class="text-right">{{ $totalOut }}</td>
            <td class="text-right">{{ $runningBalance }}</td>
         </tr>
      </tbody>
   </table>
</body>

</html>
