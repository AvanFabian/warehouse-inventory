<?php

// Translation mappings for warehouse inventory system
$translations = [
    // Navigation & Menu
    'Dashboard' => 'Dashboard',
    'Products' => 'Produk',
    'Categories' => 'Kategori',
    'Suppliers' => 'Pemasok',
    'Warehouses' => 'Gudang',
    'Stock In' => 'Stok Masuk',
    'Stock Out' => 'Stok Keluar',
    'Stock Opname' => 'Stok Opname',
    'Warehouse Transfers' => 'Transfer Antar Gudang',
    'All Reports' => 'Semua Laporan',
    'User Management' => 'Manajemen Pengguna',
    'Settings' => 'Pengaturan',

    // Actions
    'New Product' => 'Produk Baru',
    'New Category' => 'Kategori Baru',
    'New Supplier' => 'Pemasok Baru',
    'New Warehouse' => 'Gudang Baru',
    'Export Excel' => 'Ekspor Excel',
    'Print Labels' => 'Cetak Label',
    'Import' => 'Impor',
    'Export' => 'Ekspor',
    'Filter' => 'Filter',
    'Search' => 'Cari',
    'View' => 'Lihat',
    'Edit' => 'Edit',
    'Delete' => 'Hapus',
    'Save' => 'Simpan',
    'Cancel' => 'Batal',
    'Back' => 'Kembali',
    'Submit' => 'Kirim',
    'Create' => 'Buat',
    'Update' => 'Perbarui',

    // Table Headers
    'Code' => 'Kode',
    'Name' => 'Nama',
    'Description' => 'Deskripsi',
    'Warehouse' => 'Gudang',
    'Category' => 'Kategori',
    'Stock' => 'Stok',
    'Min Stock' => 'Stok Minimum',
    'Unit' => 'Satuan',
    'Status' => 'Status',
    'Actions' => 'Aksi',
    'Address' => 'Alamat',
    'Phone' => 'Telepon',
    'Email' => 'Email',
    'Contact Person' => 'Kontak Person',
    'Price' => 'Harga',
    'Purchase Price' => 'Harga Beli',
    'Selling Price' => 'Harga Jual',
    'Quantity' => 'Jumlah',
    'Date' => 'Tanggal',

    // Status
    'Active' => 'Aktif',
    'Inactive' => 'Tidak Aktif',
    'Pending' => 'Menunggu',
    'Approved' => 'Disetujui',
    'Rejected' => 'Ditolak',
    'In Transit' => 'Dalam Perjalanan',
    'Completed' => 'Selesai',

    // Filters & Placeholders
    'All Warehouses' => 'Semua Gudang',
    'All Categories' => 'Semua Kategori',
    'All Status' => 'Semua Status',
    'Search name or code...' => 'Cari nama atau kode...',
    'No data available' => 'Tidak ada data',

    // Messages
    'Are you sure?' => 'Apakah Anda yakin?',
    'Delete this product?' => 'Hapus produk ini?',
    'Delete this category?' => 'Hapus kategori ini?',
    'Delete this supplier?' => 'Hapus pemasok ini?',
];

// Output JSON format for easy use
echo json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);