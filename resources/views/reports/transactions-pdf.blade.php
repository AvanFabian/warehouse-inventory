<!DOCTYPE html>
<html>

<head>
   <meta charset="utf-8">
   <title>Transaction Report - {{ date('d M Y') }}</title>
   <style>
      body {
         font-family: Arial, sans-serif;
         font-size: 12px;
      }

      h1 {
         font-size: 18px;
         text-align: center;
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

      .summary {
         margin-top: 20px;
         background-color: #f9fafb;
         padding: 10px;
         border-radius: 5px;
      }
   </style>
</head>

<body>
   <h1>Transaction Report</h1>
   <p style="text-align: center;">
      @if (request('from') && request('to'))
         Period: {{ \Carbon\Carbon::parse(request('from'))->format('d M Y') }} -
         {{ \Carbon\Carbon::parse(request('to'))->format('d M Y') }}
      @else
         All Transactions
      @endif
   </p>
   <p style="text-align: center;">Generated on {{ date('d F Y H:i') }}</p>

   <div class="summary">
      <strong>Summary:</strong><br>
      Total Stock In: {{ $stats['total_in'] }} transactions<br>
      Total Stock Out: {{ $stats['total_out'] }} transactions<br>
      Total Transaction Value: Rp {{ number_format($stats['total_value']) }}
   </div>

   <table>
      <thead>
         <tr>
            <th>Date</th>
            <th>Code</th>
            <th>Type</th>
            <th>Supplier/Customer</th>
            <th class="text-right">Total Items</th>
            <th class="text-right">Grand Total</th>
         </tr>
      </thead>
      <tbody>
         @foreach ($allTransactions as $txn)
            <tr>
               <td>{{ $txn->transaction_date->format('d M Y') }}</td>
               <td>{{ $txn->transaction_code }}</td>
               <td>{{ $txn->type === 'in' ? 'Stock In' : 'Stock Out' }}</td>
               <td>
                  @if ($txn->type === 'in')
                     {{ $txn->supplier?->name ?? '-' }}
                  @else
                     {{ $txn->customer ?? '-' }}
                  @endif
               </td>
               <td class="text-right">{{ $txn->details->sum('quantity') ?? 0 }}</td>
               <td class="text-right">Rp {{ number_format($txn->total) }}</td>
            </tr>
         @endforeach
      </tbody>
   </table>
</body>

</html>
