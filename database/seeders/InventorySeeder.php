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
        // 1. Create Suppliers first
        $suppliers = [
            ['name' => 'Office Supplies Inc', 'contact_person' => 'John Smith', 'email' => 'john@officesupplies.com', 'phone' => '+1234567890', 'address' => '123 Business St, NY'],
            ['name' => 'Tech Equipment Co', 'contact_person' => 'Sarah Johnson', 'email' => 'sarah@techequip.com', 'phone' => '+1234567891', 'address' => '456 Tech Ave, CA'],
            ['name' => 'School Furniture Ltd', 'contact_person' => 'Mike Brown', 'email' => 'mike@schoolfurn.com', 'phone' => '+1234567892', 'address' => '789 Furniture Rd, TX'],
            ['name' => 'Lab Supplies Pro', 'contact_person' => 'Emily Davis', 'email' => 'emily@labsupplies.com', 'phone' => '+1234567893', 'address' => '321 Science Blvd, MA'],
            ['name' => 'Sports & PE Suppliers', 'contact_person' => 'David Wilson', 'email' => 'david@sportssuppliers.com', 'phone' => '+1234567894', 'address' => '654 Athletic Way, FL']
        ];

        foreach ($suppliers as $supplier) {
            Supplier::firstOrCreate(['name' => $supplier['name']], $supplier);
        }

        // 2. Create Inventory Categories
        $categories = [
            ['name' => 'Electronics', 'description' => 'Electronic devices and equipment for classrooms'],
            ['name' => 'Furniture', 'description' => 'School furniture including desks, chairs, and cabinets'],
            ['name' => 'Stationery', 'description' => 'Office and classroom stationery supplies'],
            ['name' => 'Lab Equipment', 'description' => 'Science laboratory equipment and consumables'],
            ['name' => 'Sports Equipment', 'description' => 'Physical education and sports equipment'],
            ['name' => 'Cleaning Supplies', 'description' => 'Cleaning and sanitation products'],
            ['name' => 'Kitchen Supplies', 'description' => 'Cafeteria and kitchen equipment']
        ];

        foreach ($categories as $category) {
            InventoryCategory::firstOrCreate(['name' => $category['name']], $category);
        }

        // 3. Create Inventory Items
        $cats = InventoryCategory::all();
        $sups = Supplier::all();

        $items = [
            // Electronics
            ['name' => 'Projector - Epson EB-X41', 'category' => 'Electronics', 'quantity' => 15, 'unit' => 'pcs', 'minimum_quantity' => 5, 'cost_per_unit' => 450.00, 'location' => 'Storage Room A', 'description' => 'XGA 3LCD projector for classrooms'],
            ['name' => 'Laptop - Dell Latitude 3520', 'category' => 'Electronics', 'quantity' => 25, 'unit' => 'pcs', 'minimum_quantity' => 10, 'cost_per_unit' => 650.00, 'location' => 'IT Department', 'description' => 'Student laptops for computer lab'],
            ['name' => 'Interactive Whiteboard', 'category' => 'Electronics', 'quantity' => 8, 'unit' => 'pcs', 'minimum_quantity' => 3, 'cost_per_unit' => 1200.00, 'location' => 'Storage Room A', 'description' => 'Smart boards for interactive learning'],
            ['name' => 'Document Camera', 'category' => 'Electronics', 'quantity' => 12, 'unit' => 'pcs', 'minimum_quantity' => 5, 'cost_per_unit' => 280.00, 'location' => 'AV Room', 'description' => 'Digital document cameras'],
            ['name' => 'Printer - HP LaserJet', 'category' => 'Electronics', 'quantity' => 10, 'unit' => 'pcs', 'minimum_quantity' => 4, 'cost_per_unit' => 320.00, 'location' => 'Admin Office', 'description' => 'Laser printers for offices'],

            // Furniture
            ['name' => 'Student Desk', 'category' => 'Furniture', 'quantity' => 150, 'unit' => 'pcs', 'minimum_quantity' => 50, 'cost_per_unit' => 85.00, 'location' => 'Warehouse B', 'description' => 'Individual student desks'],
            ['name' => 'Student Chair', 'category' => 'Furniture', 'quantity' => 150, 'unit' => 'pcs', 'minimum_quantity' => 50, 'cost_per_unit' => 45.00, 'location' => 'Warehouse B', 'description' => 'Ergonomic student chairs'],
            ['name' => 'Teacher Desk', 'category' => 'Furniture', 'quantity' => 25, 'unit' => 'pcs', 'minimum_quantity' => 10, 'cost_per_unit' => 195.00, 'location' => 'Warehouse B', 'description' => 'Office desk for teachers'],
            ['name' => 'Filing Cabinet', 'category' => 'Furniture', 'quantity' => 20, 'unit' => 'pcs', 'minimum_quantity' => 5, 'cost_per_unit' => 150.00, 'location' => 'Warehouse B', 'description' => '4-drawer filing cabinets'],
            ['name' => 'Bookshelf', 'category' => 'Furniture', 'quantity' => 30, 'unit' => 'pcs', 'minimum_quantity' => 10, 'cost_per_unit' => 120.00, 'location' => 'Warehouse B', 'description' => 'Library and classroom bookshelves'],

            // Stationery
            ['name' => 'Whiteboard Markers', 'category' => 'Stationery', 'quantity' => 200, 'unit' => 'boxes', 'minimum_quantity' => 50, 'cost_per_unit' => 8.50, 'location' => 'Stationery Room', 'description' => 'Assorted color markers - 12 per box'],
            ['name' => 'A4 Paper Reams', 'category' => 'Stationery', 'quantity' => 100, 'unit' => 'reams', 'minimum_quantity' => 30, 'cost_per_unit' => 4.50, 'location' => 'Stationery Room', 'description' => 'White A4 printing paper - 500 sheets per ream'],
            ['name' => 'Pencils', 'category' => 'Stationery', 'quantity' => 500, 'unit' => 'boxes', 'minimum_quantity' => 100, 'cost_per_unit' => 3.00, 'location' => 'Stationery Room', 'description' => 'HB pencils - 12 per box'],
            ['name' => 'Notebooks - A5', 'category' => 'Stationery', 'quantity' => 300, 'unit' => 'pcs', 'minimum_quantity' => 100, 'cost_per_unit' => 1.50, 'location' => 'Stationery Room', 'description' => '100-page ruled notebooks'],
            ['name' => 'Correction Fluid', 'category' => 'Stationery', 'quantity' => 80, 'unit' => 'bottles', 'minimum_quantity' => 20, 'cost_per_unit' => 1.20, 'location' => 'Stationery Room', 'description' => 'White correction fluid'],

            // Lab Equipment
            ['name' => 'Microscope - Compound', 'category' => 'Lab Equipment', 'quantity' => 20, 'unit' => 'pcs', 'minimum_quantity' => 8, 'cost_per_unit' => 580.00, 'location' => 'Science Lab', 'description' => 'Binocular compound microscopes'],
            ['name' => 'Beaker Set', 'category' => 'Lab Equipment', 'quantity' => 50, 'unit' => 'sets', 'minimum_quantity' => 15, 'cost_per_unit' => 25.00, 'location' => 'Science Lab', 'description' => 'Glass beaker sets (50ml-1000ml)'],
            ['name' => 'Test Tubes', 'category' => 'Lab Equipment', 'quantity' => 500, 'unit' => 'pcs', 'minimum_quantity' => 100, 'cost_per_unit' => 0.50, 'location' => 'Science Lab', 'description' => 'Borosilicate glass test tubes'],
            ['name' => 'Safety Goggles', 'category' => 'Lab Equipment', 'quantity' => 100, 'unit' => 'pcs', 'minimum_quantity' => 30, 'cost_per_unit' => 5.00, 'location' => 'Science Lab', 'description' => 'Chemical splash safety goggles'],
            ['name' => 'Bunsen Burner', 'category' => 'Lab Equipment', 'quantity' => 25, 'unit' => 'pcs', 'minimum_quantity' => 10, 'cost_per_unit' => 35.00, 'location' => 'Science Lab', 'description' => 'Gas bunsen burners'],

            // Sports Equipment
            ['name' => 'Basketball', 'category' => 'Sports Equipment', 'quantity' => 30, 'unit' => 'pcs', 'minimum_quantity' => 15, 'cost_per_unit' => 25.00, 'location' => 'Sports Storage', 'description' => 'Official size basketballs'],
            ['name' => 'Football (Soccer)', 'category' => 'Sports Equipment', 'quantity' => 25, 'unit' => 'pcs', 'minimum_quantity' => 12, 'cost_per_unit' => 20.00, 'location' => 'Sports Storage', 'description' => 'Size 5 soccer balls'],
            ['name' => 'Volleyball', 'category' => 'Sports Equipment', 'quantity' => 20, 'unit' => 'pcs', 'minimum_quantity' => 10, 'cost_per_unit' => 18.00, 'location' => 'Sports Storage', 'description' => 'Official volleyball balls'],
            ['name' => 'Table Tennis Table', 'category' => 'Sports Equipment', 'quantity' => 5, 'unit' => 'pcs', 'minimum_quantity' => 2, 'cost_per_unit' => 450.00, 'location' => 'Sports Hall', 'description' => 'Competition table tennis tables'],
            ['name' => 'Yoga Mats', 'category' => 'Sports Equipment', 'quantity' => 40, 'unit' => 'pcs', 'minimum_quantity' => 15, 'cost_per_unit' => 12.00, 'location' => 'Sports Storage', 'description' => 'Non-slip yoga/exercise mats'],

            // Cleaning Supplies
            ['name' => 'Disinfectant Spray', 'category' => 'Cleaning Supplies', 'quantity' => 60, 'unit' => 'bottles', 'minimum_quantity' => 20, 'cost_per_unit' => 6.50, 'location' => 'Janitorial Room', 'description' => '500ml disinfectant spray bottles'],
            ['name' => 'Mop and Bucket Set', 'category' => 'Cleaning Supplies', 'quantity' => 15, 'unit' => 'sets', 'minimum_quantity' => 5, 'cost_per_unit' => 28.00, 'location' => 'Janitorial Room', 'description' => 'Complete mop and bucket cleaning sets'],
            ['name' => 'Toilet Paper - Industrial', 'category' => 'Cleaning Supplies', 'quantity' => 200, 'unit' => 'rolls', 'minimum_quantity' => 50, 'cost_per_unit' => 1.20, 'location' => 'Janitorial Room', 'description' => 'Large roll toilet paper'],
            ['name' => 'Hand Soap', 'category' => 'Cleaning Supplies', 'quantity' => 80, 'unit' => 'bottles', 'minimum_quantity' => 25, 'cost_per_unit' => 4.00, 'location' => 'Janitorial Room', 'description' => 'Liquid hand soap dispenser refills'],

            // Kitchen Supplies
            ['name' => 'Plates - Ceramic', 'category' => 'Kitchen Supplies', 'quantity' => 200, 'unit' => 'pcs', 'minimum_quantity' => 50, 'cost_per_unit' => 3.50, 'location' => 'Cafeteria Storage', 'description' => 'Durable ceramic dinner plates'],
            ['name' => 'Cutlery Set', 'category' => 'Kitchen Supplies', 'quantity' => 100, 'unit' => 'sets', 'minimum_quantity' => 30, 'cost_per_unit' => 8.00, 'location' => ' Cafeteria Storage', 'description' => 'Fork, knife, spoon sets'],
            ['name' => 'Food Storage Containers', 'category' => 'Kitchen Supplies', 'quantity' => 50, 'unit' => 'sets', 'minimum_quantity' => 15, 'cost_per_unit' => 15.00, 'location' => 'Cafeteria Storage', 'description' => 'Various size plastic containers'],
        ];

        foreach ($items as $itemData) {
            $category = $cats->firstWhere('name', $itemData['category']);
            $supplier = $sups->random(); // Randomly assign supplier

            if ($category) {
                InventoryItem::create([
                    'name' => $itemData['name'],
                    'category_id' => $category->id,
                    'quantity' => $itemData['quantity'],
                    'unit' => $itemData['unit'],
                    'minimum_quantity' => $itemData['minimum_quantity'],
                    'cost_per_unit' => $itemData['cost_per_unit'],
                    'supplier_id' => $supplier->id,
                    'location' => $itemData['location'],
                    'description' => $itemData['description']
                ]);
            }
        }
    }
}
