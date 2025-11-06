<!DOCTYPE html>
<html>

<head>
   <meta charset="utf-8">
   <title>Stock Report - {{ date('d M Y') }}</title>
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

      .low-stock {
         background-color: #fee2e2;
         color: #991b1b;
      }
   </style>
</head>

<body>
   <h1>Current Stock Report</h1>
   <p style="text-align: center;">Generated on {{ date('d F Y H:i') }}</p>

   <table>
      <thead>
         <tr>
            <th>Code</th>
            <th>Product Name</th>
            <th>Category</th>
            <th class="text-right">Stock</th>
            <th class="text-right">Min</th>
            <th>Unit</th>
            <th>Location</th>
         </tr>
      </thead>
      <tbody>
         @foreach ($products as $p)
            <tr class="{{ $p->stock < $p->min_stock ? 'low-stock' : '' }}">
               <td>{{ $p->code }}</td>
               <td>{{ $p->name }}</td>
               <td>{{ $p->category?->name ?? '-' }}</td>
               <td class="text-right">{{ $p->stock }}</td>
               <td class="text-right">{{ $p->min_stock }}</td>
               <td>{{ $p->unit }}</td>
               <td>{{ $p->rack_location ?? '-' }}</td>
            </tr>
         @endforeach
      </tbody>
   </table>
</body>

</html>
