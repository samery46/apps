<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AssetRequest;

class AssetRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //     AssetRequest::create([
        //     'plant_id' => 1,
        //     'created_at' => now(),
        //     'asset_group_id' => 5,
        //     'fixed_asset_number' => 'FA-12345',
        //     'cea_number' => 'CEA-67890',
        //     'cost_center_id' => 2,
        //     'type' => 'Equipment',
        //     'sub_asset_number' => 'SA-001',
        //     'usage_period' => '5 years',
        //     'quantity' => 10,
        //     'condition' => 'New',
        //     'item_name' => 'Industrial Pump',
        //     'country_of_origin' => 'Germany',
        //     'year_of_manufacture' => 2023,
        //     'supplier' => 'ABC Supplier',
        //     'expected_arrival' => now()->addDays(30),
        //     'expected_usage' => 'Production Line',
        //     'location' => 'Factory A',
        //     'description' => 'Heavy-duty pump for industrial use',
        //     'status' => 'Pending',
        //     'is_aktif' => true,
        //     'user_id' => 3,
        // ]);


        AssetRequest::create([
        'plant_id' => 13,
        'created_at' => now(),
        'asset_group_id' => 8,
        'cea_number' => '013/CEA/J312 - PLB/MFG/2025',
        'cost_center_id' => 250,
        'type' => 'Aktiva Tetap',
        'condition' => 'Baru',
        'item_name' => 'TDS meter merk eutech',
        'status' => 'pending',
        'is_aktif' => true,
        'user_id' => 37,


        ]);

        // jalankan seeder
        // php artisan db:seed --class=AssetRequestSeeder
    }
}

