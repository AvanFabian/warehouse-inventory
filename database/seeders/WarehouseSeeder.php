<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Warehouse;
use App\Models\User;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        // Create default warehouse
        Warehouse::create([
            'name' => 'Gudang Utama',
            'code' => 'GU-001',
            'address' => 'Jl. Raya Jakarta No. 123',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'postal_code' => '12345',
            'phone' => '021-1234567',
            'email' => 'gudang@example.com',
            'is_active' => true,
            'is_default' => true,
            'created_by' => $admin ? $admin->id : null,
        ]);

        // Create additional warehouses
        Warehouse::create([
            'name' => 'Gudang Cabang',
            'code' => 'GC-001',
            'address' => 'Jl. Raya Bandung No. 456',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
            'postal_code' => '40123',
            'phone' => '022-7654321',
            'email' => 'cabang@example.com',
            'is_active' => true,
            'is_default' => false,
            'created_by' => $admin ? $admin->id : null,
        ]);
    }
}
