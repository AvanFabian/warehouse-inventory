$replacements = @{
    '"Products"' = '"Produk"'
    '"New Product"' = '"Produk Baru"'
    '"Export Excel"' = '"Ekspor Excel"'
    '"Print Labels"' = '"Cetak Label"'
    'Search name or code' = 'Cari nama atau kode'
    '"Code"' = '"Kode"'
    '"Name"' = '"Nama"'
    '"Warehouse"' = '"Gudang"'
    '"Category"' = '"Kategori"'
    '"Stock"' = '"Stok"'
    '"Min Stock"' = '"Stok Minimum"'
    '"Unit"' = '"Satuan"'
    '"Status"' = '"Status"'
    '"Actions"' = '"Aksi"'
    'title="View"' = 'title="Lihat"'
    '>View<' = '>Lihat<'
    'title="Edit"' = 'title="Edit"'
    '>Edit<' = '>Edit<'
    'title="Delete"' = 'title="Hapus"'
    '>Delete<' = '>Hapus<'
    '"Active"' = '"Aktif"'
    '"Inactive"' = '"Tidak Aktif"'
    'No data available' = 'Tidak ada data'
}

$files = Get-ChildItem -Path "resources\views\products" -Filter "*.blade.php" -Recurse

foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw -Encoding UTF8
    $modified = $false
    
    foreach ($key in $replacements.Keys) {
        if ($content -match [regex]::Escape($key)) {
            $content = $content -replace [regex]::Escape($key), $replacements[$key]
            $modified = $true
        }
    }
    
    if ($modified) {
        Set-Content -Path $file.FullName -Value $content -Encoding UTF8 -NoNewline
        Write-Host "Updated: $($file.Name)"
    }
}
