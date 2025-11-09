<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run()
    {
        // Get warehouses
        $warehouses = Warehouse::all();
        // Seed Categories
        $categories = [
            ['name' => 'Electronics', 'description' => 'Electronic devices and components', 'status' => true],
            ['name' => 'Furniture', 'description' => 'Office and home furniture', 'status' => true],
            ['name' => 'Stationery', 'description' => 'Office supplies and stationery', 'status' => true],
            ['name' => 'Tools', 'description' => 'Hardware and tools', 'status' => true],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }

        // Seed Suppliers
        $suppliers = [
            [
                'name' => 'PT. Elektronik Jaya',
                'address' => 'Jl. Industri No. 123, Jakarta',
                'phone' => '021-12345678',
                'email' => 'info@elektronikjaya.com',
                'contact_person' => 'Budi Santoso',
            ],
            [
                'name' => 'CV. Furniture Indo',
                'address' => 'Jl. Mebel Raya No. 45, Surabaya',
                'phone' => '031-87654321',
                'email' => 'sales@furnitureindo.co.id',
                'contact_person' => 'Siti Rahayu',
            ],
            [
                'name' => 'Toko Alat Tulis Sejahtera',
                'address' => 'Jl. Perkantoran No. 78, Bandung',
                'phone' => '022-55556666',
                'email' => 'atk@sejahtera.com',
                'contact_person' => 'Ahmad Wijaya',
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }

        // Seed Sample Products for each warehouse
        $productsTemplate = [
            [
                'code' => 'ELC-001',
                'name' => 'Laptop Dell Inspiron 15',
                'category_id' => 1,
                'unit' => 'pcs',
                'min_stock' => 5,
                'purchase_price' => 8000000,
                'selling_price' => 9500000,
                'stock' => 0,
                'rack_location' => 'A-01-01',
                'status' => true,
            ],
            [
                'code' => 'FRN-001',
                'name' => 'Office Chair Ergonomic',
                'category_id' => 2,
                'unit' => 'pcs',
                'min_stock' => 10,
                'purchase_price' => 1500000,
                'selling_price' => 1800000,
                'stock' => 0,
                'rack_location' => 'B-02-05',
                'status' => true,
            ],
            [
                'code' => 'STN-001',
                'name' => 'Ballpoint Pen Blue (Box)',
                'category_id' => 3,
                'unit' => 'box',
                'min_stock' => 20,
                'purchase_price' => 50000,
                'selling_price' => 65000,
                'stock' => 0,
                'rack_location' => 'C-03-10',
                'status' => true,
            ],
        ];

        // Create products for each warehouse
        foreach ($warehouses as $warehouse) {
            foreach ($productsTemplate as $productData) {
                // Add warehouse-specific code suffix
                $product = $productData;
                $product['code'] = $productData['code'] . '-' . $warehouse->code;
                $product['warehouse_id'] = $warehouse->id;

                Product::create($product);
            }
        }
    }
}