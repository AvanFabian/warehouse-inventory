<!-- Barcode Scanner Component -->
<div class="bg-white p-4 rounded-lg shadow-sm border">
   <div class="flex items-center justify-between mb-3">
      <h3 class="text-lg font-semibold">Barcode Scanner</h3>
      <button type="button" onclick="toggleScanner()" id="scannerToggle"
         class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
         <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
               d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z">
            </path>
         </svg>
         Start Camera
      </button>
   </div>

   <!-- Camera Scanner -->
   <div id="scannerArea" style="display: none;">
      <div id="video-container" class="relative mb-3">
         <video id="barcode-scanner" width="100%" style="max-width: 500px; border-radius: 8px;"></video>
         <canvas id="barcode-canvas" style="display: none;"></canvas>
      </div>
      <div id="scanner-status" class="text-sm text-gray-600 mb-2">Camera ready. Point at a barcode...</div>
   </div>

   <!-- Manual Input -->
   <div class="mt-3">
      <label class="block text-sm font-medium text-gray-700 mb-1">Or enter barcode manually:</label>
      <div class="flex gap-2">
         <input type="text" id="manual-barcode-input" placeholder="Scan or type product code..."
            class="flex-1 border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" autofocus>
         <button type="button" onclick="searchBarcode()"
            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
            Search
         </button>
      </div>
   </div>

   <!-- Scanned Product Info -->
   <div id="scanned-product-info" class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded" style="display: none;">
      <h4 class="font-semibold text-blue-900 mb-2">Product Found:</h4>
      <div id="product-details" class="text-sm"></div>
      <button type="button" onclick="addScannedProduct()"
         class="mt-3 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 w-full">
         Add to Transaction
      </button>
   </div>

   <div id="scan-error" class="mt-4 p-3 bg-red-50 border border-red-200 rounded text-red-700 text-sm"
      style="display: none;"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@zxing/library@latest"></script>

<script>
   let codeReader;
   let selectedDeviceId;
   let scannedProduct = null;
   let isScanning = false;

   // Initialize manual input listener
   document.getElementById('manual-barcode-input').addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
         e.preventDefault();
         searchBarcode();
      }
   });

   function toggleScanner() {
      const scannerArea = document.getElementById('scannerArea');
      const toggleBtn = document.getElementById('scannerToggle');

      if (isScanning) {
         stopScanner();
         scannerArea.style.display = 'none';
         toggleBtn.innerHTML = `
            <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
            </svg>
            Start Camera`;
         isScanning = false;
      } else {
         startScanner();
         scannerArea.style.display = 'block';
         toggleBtn.innerHTML = `
            <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            Stop Camera`;
         isScanning = true;
      }
   }

   function startScanner() {
      codeReader = new ZXing.BrowserMultiFormatReader();

      codeReader.listVideoInputDevices()
         .then(videoInputDevices => {
            selectedDeviceId = videoInputDevices[0].deviceId;

            codeReader.decodeFromVideoDevice(selectedDeviceId, 'barcode-scanner', (result, err) => {
               if (result) {
                  document.getElementById('scanner-status').textContent = `Barcode detected: ${result.text}`;
                  document.getElementById('manual-barcode-input').value = result.text;
                  searchBarcode();
                  // Optionally stop scanner after successful scan
                  // stopScanner();
               }
               if (err && err.name !== 'NotFoundException') {
                  console.error(err);
               }
            });
         })
         .catch(err => {
            console.error(err);
            document.getElementById('scanner-status').textContent =
               'Error accessing camera. Please check permissions.';
         });
   }

   function stopScanner() {
      if (codeReader) {
         codeReader.reset();
         codeReader = null;
      }
   }

   function searchBarcode() {
      const code = document.getElementById('manual-barcode-input').value.trim();

      if (!code) {
         showError('Please enter a barcode');
         return;
      }

      hideError();
      hideProductInfo();

      fetch('{{ route('barcode.scan') }}', {
            method: 'POST',
            headers: {
               'Content-Type': 'application/json',
               'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
               code: code
            })
         })
         .then(response => response.json())
         .then(data => {
            if (data.success) {
               scannedProduct = data.product;
               displayProductInfo(data.product);
            } else {
               showError(data.error || 'Product not found');
            }
         })
         .catch(error => {
            console.error('Error:', error);
            showError('Error searching for product');
         });
   }

   function displayProductInfo(product) {
      const detailsHtml = `
        <div class="grid grid-cols-2 gap-2">
            <div><strong>Code:</strong> ${product.code}</div>
            <div><strong>Name:</strong> ${product.name}</div>
            <div><strong>Category:</strong> ${product.category}</div>
            <div><strong>Warehouse:</strong> ${product.warehouse}</div>
            <div><strong>Stock:</strong> ${product.stock} ${product.unit}</div>
            <div><strong>Price:</strong> Rp ${new Intl.NumberFormat('id-ID').format(product.selling_price)}</div>
        </div>
    `;

      document.getElementById('product-details').innerHTML = detailsHtml;
      document.getElementById('scanned-product-info').style.display = 'block';
   }

   function hideProductInfo() {
      document.getElementById('scanned-product-info').style.display = 'none';
   }

   function showError(message) {
      const errorDiv = document.getElementById('scan-error');
      errorDiv.textContent = message;
      errorDiv.style.display = 'block';
   }

   function hideError() {
      document.getElementById('scan-error').style.display = 'none';
   }

   // This function should be customized based on your form structure
   function addScannedProduct() {
      if (!scannedProduct) return;

      // Check if we're on stock-in or stock-out page and handle accordingly
      if (typeof addProductRow === 'function') {
         addProductRow(scannedProduct);
      } else {
         alert('Product found! Manual addition required.');
      }

      // Clear input and hide info
      document.getElementById('manual-barcode-input').value = '';
      hideProductInfo();
   }

   // Cleanup on page unload
   window.addEventListener('beforeunload', function() {
      stopScanner();
   });
</script>
