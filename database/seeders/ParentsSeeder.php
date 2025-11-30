<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Parents;

class ParentsSeeder extends Seeder
{
    public function run(): void
    {
        $parents = [
            // Parents for Grade 1 Students
            [
                'first_name' => 'David',
                'last_name' => 'Johnson',
                'relationship' => 'father',
                'email' => 'david.johnson@email.com',
                'phone' => '555-1001',
                'alternate_phone' => '555-1101',
                'occupation' => 'Software Engineer',
            ],
            [
                'first_name' => 'Emily',
                'last_name' => 'Johnson',
                'relationship' => 'mother',
                'email' => 'emily.johnson@email.com',
                'phone' => '555-1002',
                'alternate_phone' => '555-1102',
                'occupation' => 'Marketing Manager',
            ],
            [
                'first_name' => 'Carlos',
                'last_name' => 'Martinez',
                'relationship' => 'father',
                'email' => 'carlos.martinez@email.com',
                'phone' => '555-1003',
                'alternate_phone' => '555-1103',
                'occupation' => 'Restaurant Owner',
            ],
            [
                'first_name' => 'Maria',
                'last_name' => 'Martinez',
                'relationship' => 'mother',
                'email' => 'maria.martinez@email.com',
                'phone' => '555-1004',
                'alternate_phone' => '555-1104',
                'occupation' => 'Nurse',
            ],
            [
                'first_name' => 'Robert',
                'last_name' => 'Brown',
                'relationship' => 'father',
                'email' => 'robert.brown@email.com',
                'phone' => '555-1005',
                'alternate_phone' => '555-1105',
                'occupation' => 'Teacher',
            ],
            
            // Parents for Grade 2 Students
            [
                'first_name' => 'Jennifer',
                'last_name' => 'Davis',
                'relationship' => 'mother',
                'email' => 'jennifer.davis@email.com',
                'phone' => '555-1006',
                'alternate_phone' => '555-1106',
                'occupation' => 'Accountant',
            ],
            [
                'first_name' => 'Michael',
                'last_name' => 'Wilson',
                'relationship' => 'father',
                'email' => 'michael.wilson@email.com',
                'phone' => '555-1007',
                'alternate_phone' => '555-1107',
                'occupation' => 'Police Officer',
            ],
            [
                'first_name' => 'Lisa',
                'last_name' => 'Garcia',
                'relationship' => 'mother',
                'email' => 'lisa.garcia@email.com',
                'phone' => '555-1008',
                'alternate_phone' => '555-1108',
                'occupation' => 'Doctor',
            ],
            
            // Parents for Grade 3 Students
            [
                'first_name' => 'Antonio',
                'last_name' => 'Rodriguez',
                'relationship' => 'father',
                'email' => 'antonio.rodriguez@email.com',
                'phone' => '555-1009',
                'alternate_phone' => '555-1109',
                'occupation' => 'Construction Manager',
            ],
            [
                'first_name' => 'Patricia',
                'last_name' => 'Lewis',
                'relationship' => 'mother',
                'email' => 'patricia.lewis@email.com',
                'phone' => '555-1010',
                'alternate_phone' => '555-1110',
                'occupation' => 'Lawyer',
            ],
            [
                'first_name' => 'Kevin',
                'last_name' => 'Lee',
                'relationship' => 'father',
                'email' => 'kevin.lee@email.com',
                'phone' => '555-1011',
                'alternate_phone' => '555-1111',
                'occupation' => 'IT Consultant',
            ],
            
            // Parents for Grade 4 Students
            [
                'first_name' => 'Sarah',
                'last_name' => 'Walker',
                'relationship' => 'mother',
                'email' => 'sarah.walker@email.com',
                'phone' => '555-1012',
                'alternate_phone' => '555-1112',
                'occupation' => 'Pharmacist',
            ],
            [
                'first_name' => 'Thomas',
                'last_name' => 'Hall',
                'relationship' => 'father',
                'email' => 'thomas.hall@email.com',
                'phone' => '555-1013',
                'alternate_phone' => '555-1113',
                'occupation' => 'Electrician',
            ],
            [
                'first_name' => 'Michelle',
                'last_name' => 'Allen',
                'relationship' => 'mother',
                'email' => 'michelle.allen@email.com',
                'phone' => '555-1014',
                'alternate_phone' => '555-1114',
                'occupation' => 'Graphic Designer',
            ],
            
            // Parents for Grade 5 Students
            [
                'first_name' => 'Christopher',
                'last_name' => 'Young',
                'relationship' => 'father',
                'email' => 'christopher.young@email.com',
                'phone' => '555-1015',
                'alternate_phone' => '555-1115',
                'occupation' => 'Business Owner',
            ],
            [
                'first_name' => 'Amanda',
                'last_name' => 'Hernandez',
                'relationship' => 'mother',
                'email' => 'amanda.hernandez@email.com',
                'phone' => '555-1016',
                'alternate_phone' => '555-1116',
                'occupation' => 'Real Estate Agent',
            ],
            [
                'first_name' => 'Daniel',
                'last_name' => 'King',
                'relationship' => 'father',
                'email' => 'daniel.king@email.com',
                'phone' => '555-1017',
                'alternate_phone' => '555-1117',
                'occupation' => 'Mechanic',
            ],
            
            // Guardian for Alumni Student
            [
                'first_name' => 'Jessica',
                'last_name' => 'Wright',
                'relationship' => 'mother',
                'email' => 'jessica.wright@email.com',
                'phone' => '555-1018',
                'alternate_phone' => '555-1118',
                'occupation' => 'Social Worker',
            ],
            [
                'first_name' => 'Richard',
                'last_name' => 'Lopez',
                'relationship' => 'father',
                'email' => 'richard.lopez@email.com',
                'phone' => '555-1019',
                'alternate_phone' => '555-1119',
                'occupation' => 'Chef',
            ],
            
            // Guardian for Transferred/Inactive Students
            [
                'first_name' => 'Nicole',
                'last_name' => 'Hill',
                'relationship' => 'mother',
                'email' => 'nicole.hill@email.com',
                'phone' => '555-1020',
                'alternate_phone' => '555-1120',
                'occupation' => 'Bank Manager',
            ],
            [
                'first_name' => 'Melissa',
                'last_name' => 'Green',
                'relationship' => 'mother',
                'email' => 'melissa.green@email.com',
                'phone' => '555-1021',
                'alternate_phone' => '555-1121',
                'occupation' => 'Veterinarian',
            ],
        ];

        foreach ($parents as $data) {
            Parents::firstOrCreate(
                ['email' => $data['email']],
                $data
            );
        }
    }
}