<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\Supplier;
use Carbon\Carbon;

class InventorySeeder extends Seeder
{
    public function run()
    {
        // 1. Kenyan suppliers
        $suppliers = [
            ['name' => 'Jua Kali Stationers Ltd', 'contact_person' => 'Grace Wanjiru', 'email' => 'sales@juakalistationers.co.ke', 'phone' => '0722123001', 'address' => 'River Road, Nairobi', 'code' => 'SUP-001'],
            ['name' => 'Nairobi Lab & School Equipment', 'contact_person' => 'Peter Otieno', 'email' => 'info@nairobi-lab.co.ke', 'phone' => '0722123002', 'address' => 'Re-insurance Plaza, Nairobi', 'code' => 'SUP-002'],
            ['name' => 'CG Plaza Book Suppliers', 'contact_person' => 'Mary Achieng', 'email' => 'orders@cgplaza.co.ke', 'phone' => '0722123003', 'address' => 'Moi Avenue, Nakuru', 'code' => 'SUP-003'],
            ['name' => 'Cussons East Africa', 'contact_person' => 'Brian Wekesa', 'email' => 'procurement@cussons.co.ke', 'phone' => '0722123004', 'address' => 'Industrial Area, Nairobi', 'code' => 'SUP-004'],
            ['name' => 'Kenya School Furniture Ltd', 'contact_person' => 'Esther Njeri', 'email' => 'sales@kenyafurniture.co.ke', 'phone' => '0722123005', 'address' => 'Thika Road, Nairobi', 'code' => 'SUP-005'],
            ['name' => 'Homegrown Catering Supplies', 'contact_person' => 'Samuel Kamau', 'email' => 'supply@homegrown.co.ke', 'phone' => '0722123006', 'address' => 'Naivasha Road, Nakuru', 'code' => 'SUP-006'],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::firstOrCreate(['code' => $supplier['code']], $supplier);
        }

        // 2. Inventory categories
        $categories = [
            ['name' => 'Electronics & ICT', 'description' => 'Computers, projectors and digital learning equipment', 'code' => 'CAT-ELEC', 'category_type' => 'asset'],
            ['name' => 'Furniture', 'description' => 'Desks, chairs, cabinets and classroom furniture', 'code' => 'CAT-FURN', 'category_type' => 'asset'],
            ['name' => 'Stationery', 'description' => 'Office and classroom stationery', 'code' => 'CAT-STTN', 'category_type' => 'consumable'],
            ['name' => 'Lab Equipment', 'description' => 'Science laboratory equipment and consumables', 'code' => 'CAT-LAB', 'category_type' => 'asset'],
            ['name' => 'Sports Equipment', 'description' => 'Physical education and sports gear', 'code' => 'CAT-SPORT', 'category_type' => 'asset'],
            ['name' => 'Cleaning Supplies', 'description' => 'Cleaning and sanitation products', 'code' => 'CAT-CLEAN', 'category_type' => 'consumable'],
            ['name' => 'Kitchen Supplies', 'description' => 'Boarding kitchen and catering equipment', 'code' => 'CAT-KITCH', 'category_type' => 'consumable'],
        ];

        foreach ($categories as $category) {
            InventoryCategory::firstOrCreate(['name' => $category['name']], $category);
        }

        // 3. Inventory items (KES)
        $items = [
            ['name' => 'Projector - Epson EB-X41', 'category' => 'Electronics & ICT', 'quantity' => 15, 'unit' => 'pcs', 'minimum_quantity' => 5, 'cost_per_unit' => 65000, 'location' => 'ICT Store', 'description' => 'XGA 3LCD classroom projector', 'item_code' => 'ITM-0001'],
            ['name' => 'Student Laptop - Acer', 'category' => 'Electronics & ICT', 'quantity' => 25, 'unit' => 'pcs', 'minimum_quantity' => 8, 'cost_per_unit' => 58000, 'location' => 'ICT Lab', 'description' => 'Laptops for the computer laboratory', 'item_code' => 'ITM-0002'],
            ['name' => 'Interactive Smart Board', 'category' => 'Electronics & ICT', 'quantity' => 10, 'unit' => 'pcs', 'minimum_quantity' => 3, 'cost_per_unit' => 180000, 'location' => 'ICT Store', 'description' => 'Smart boards for CBC digital learning', 'item_code' => 'ITM-0003'],
            ['name' => 'Laser Printer - HP', 'category' => 'Electronics & ICT', 'quantity' => 12, 'unit' => 'pcs', 'minimum_quantity' => 4, 'cost_per_unit' => 42000, 'location' => 'Admin Office', 'description' => 'Laser printers for school offices', 'item_code' => 'ITM-0004'],
            ['name' => 'Student Desk', 'category' => 'Furniture', 'quantity' => 180, 'unit' => 'pcs', 'minimum_quantity' => 50, 'cost_per_unit' => 8500, 'location' => 'Warehouse', 'description' => 'Individual CBC student desks', 'item_code' => 'ITM-0005'],
            ['name' => 'Student Chair', 'category' => 'Furniture', 'quantity' => 180, 'unit' => 'pcs', 'minimum_quantity' => 50, 'cost_per_unit' => 4500, 'location' => 'Warehouse', 'description' => 'Ergonomic student chairs', 'item_code' => 'ITM-0006'],
            ['name' => 'Teacher Desk', 'category' => 'Furniture', 'quantity' => 30, 'unit' => 'pcs', 'minimum_quantity' => 10, 'cost_per_unit' => 19500, 'location' => 'Warehouse', 'description' => 'Teacher office desks', 'item_code' => 'ITM-0007'],
            ['name' => 'Filing Cabinet', 'category' => 'Furniture', 'quantity' => 24, 'unit' => 'pcs', 'minimum_quantity' => 6, 'cost_per_unit' => 15000, 'location' => 'Warehouse', 'description' => '4-drawer filing cabinets', 'item_code' => 'ITM-0008'],
            ['name' => 'Whiteboard Markers (box)', 'category' => 'Stationery', 'quantity' => 200, 'unit' => 'boxes', 'minimum_quantity' => 50, 'cost_per_unit' => 350, 'location' => 'Stationery Room', 'description' => 'Assorted dry-erase markers', 'item_code' => 'ITM-0009'],
            ['name' => 'A4 Printing Paper (ream)', 'category' => 'Stationery', 'quantity' => 120, 'unit' => 'reams', 'minimum_quantity' => 30, 'cost_per_unit' => 550, 'location' => 'Stationery Room', 'description' => 'White A4 paper, 500 sheets', 'item_code' => 'ITM-0010'],
            ['name' => 'Exercise Books A5', 'category' => 'Stationery', 'quantity' => 600, 'unit' => 'pcs', 'minimum_quantity' => 150, 'cost_per_unit' => 85, 'location' => 'Stationery Room', 'description' => 'Ruled exercise books for learners', 'item_code' => 'ITM-0011'],
            ['name' => 'Compound Microscope', 'category' => 'Lab Equipment', 'quantity' => 20, 'unit' => 'pcs', 'minimum_quantity' => 6, 'cost_per_unit' => 48000, 'location' => 'Science Lab', 'description' => 'Binocular compound microscopes', 'item_code' => 'ITM-0012'],
            ['name' => 'Beaker Set', 'category' => 'Lab Equipment', 'quantity' => 50, 'unit' => 'sets', 'minimum_quantity' => 12, 'cost_per_unit' => 2500, 'location' => 'Science Lab', 'description' => 'Glass beaker sets (50-1000ml)', 'item_code' => 'ITM-0013'],
            ['name' => 'Bunsen Burner', 'category' => 'Lab Equipment', 'quantity' => 25, 'unit' => 'pcs', 'minimum_quantity' => 8, 'cost_per_unit' => 3200, 'location' => 'Science Lab', 'description' => 'Gas burners for practical work', 'item_code' => 'ITM-0014'],
            ['name' => 'Safety Goggles', 'category' => 'Lab Equipment', 'quantity' => 100, 'unit' => 'pcs', 'minimum_quantity' => 25, 'cost_per_unit' => 450, 'location' => 'Science Lab', 'description' => 'Chemical splash protection', 'item_code' => 'ITM-0015'],
            ['name' => 'Basketball', 'category' => 'Sports Equipment', 'quantity' => 30, 'unit' => 'pcs', 'minimum_quantity' => 10, 'cost_per_unit' => 3200, 'location' => 'Sports Store', 'description' => 'Official size basketballs', 'item_code' => 'ITM-0016'],
            ['name' => 'Football (Size 5)', 'category' => 'Sports Equipment', 'quantity' => 28, 'unit' => 'pcs', 'minimum_quantity' => 10, 'cost_per_unit' => 2800, 'location' => 'Sports Store', 'description' => 'Match footballs', 'item_code' => 'ITM-0017'],
            ['name' => 'Volleyball', 'category' => 'Sports Equipment', 'quantity' => 20, 'unit' => 'pcs', 'minimum_quantity' => 8, 'cost_per_unit' => 2400, 'location' => 'Sports Store', 'description' => 'Official volleyballs', 'item_code' => 'ITM-0018'],
            ['name' => 'Disinfectant (500ml)', 'category' => 'Cleaning Supplies', 'quantity' => 80, 'unit' => 'bottles', 'minimum_quantity' => 20, 'cost_per_unit' => 380, 'location' => 'Janitorial Store', 'description' => 'Disinfectant concentrate', 'item_code' => 'ITM-0019'],
            ['name' => 'Mop & Bucket Set', 'category' => 'Cleaning Supplies', 'quantity' => 20, 'unit' => 'sets', 'minimum_quantity' => 6, 'cost_per_unit' => 1200, 'location' => 'Janitorial Store', 'description' => 'Cleaning sets for dormitories', 'item_code' => 'ITM-0020'],
            ['name' => 'Liquid Hand Soap (5L)', 'category' => 'Cleaning Supplies', 'quantity' => 100, 'unit' => 'bottles', 'minimum_quantity' => 25, 'cost_per_unit' => 650, 'location' => 'Janitorial Store', 'description' => 'Hand-washing soap for washrooms', 'item_code' => 'ITM-0021'],
            ['name' => 'Ceramic Dinner Plates', 'category' => 'Kitchen Supplies', 'quantity' => 250, 'unit' => 'pcs', 'minimum_quantity' => 60, 'cost_per_unit' => 350, 'location' => 'Kitchen Store', 'description' => 'Dining plates for boarders', 'item_code' => 'ITM-0022'],
            ['name' => 'Cutlery Set', 'category' => 'Kitchen Supplies', 'quantity' => 150, 'unit' => 'sets', 'minimum_quantity' => 40, 'cost_per_unit' => 800, 'location' => 'Kitchen Store', 'description' => 'Fork, knife and spoon sets', 'item_code' => 'ITM-0023'],
            ['name' => 'Cookware Pots (Assorted)', 'category' => 'Kitchen Supplies', 'quantity' => 40, 'unit' => 'sets', 'minimum_quantity' => 10, 'cost_per_unit' => 5200, 'location' => 'Kitchen Store', 'description' => 'Cooking pots for the boarding kitchen', 'item_code' => 'ITM-0024'],
        ];

        $cats = InventoryCategory::all();
        $sups = Supplier::all();

        foreach ($items as $itemData) {
            $category = $cats->firstWhere('name', $itemData['category']);
            if (!$category) {
                continue;
            }

            $supplier = $sups->random();

            InventoryItem::firstOrCreate(
                ['item_code' => $itemData['item_code']],
                [
                    'name' => $itemData['name'],
                    'category_id' => $category->category_id,
                    'quantity' => $itemData['quantity'],
                    'unit' => $itemData['unit'],
                    'minimum_quantity' => $itemData['minimum_quantity'],
                    'cost_per_unit' => $itemData['cost_per_unit'],
                    'supplier_id' => $supplier->supplier_id,
                    'location' => $itemData['location'],
                    'description' => $itemData['description'],
                    'purchase_date' => Carbon::now()->subMonths(4),
                    'current_condition' => 'Good',
                ]
            );
        }
    }
}
