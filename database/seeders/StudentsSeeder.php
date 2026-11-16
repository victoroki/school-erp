<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;

class StudentsSeeder extends Seeder
{
    public function run(): void
    {
        $students = [
            // PP1 (Pre-Primary 1)
            ['admission_no' => 'ADM2026/001', 'first_name' => 'Wanjiku', 'last_name' => 'Kamau', 'date_of_birth' => '2021-03-12', 'gender' => 'female', 'status' => 'active', 'enrollment_status' => 'enrolled'],
            ['admission_no' => 'ADM2026/002', 'first_name' => 'Otieno', 'last_name' => 'Odhiambo', 'date_of_birth' => '2021-08-05', 'gender' => 'male', 'status' => 'active', 'enrollment_status' => 'enrolled'],
            ['admission_no' => 'ADM2026/003', 'first_name' => 'Mwende', 'last_name' => 'Mutua', 'date_of_birth' => '2020-11-20', 'gender' => 'female', 'status' => 'active', 'enrollment_status' => 'enrolled'],

            // PP2 (Pre-Primary 2)
            ['admission_no' => 'ADM2026/004', 'first_name' => 'Kiprop', 'last_name' => 'Kipchoge', 'date_of_birth' => '2020-01-14', 'gender' => 'male', 'status' => 'active', 'enrollment_status' => 'enrolled'],
            ['admission_no' => 'ADM2026/005', 'first_name' => 'Amara', 'last_name' => 'Wafula', 'date_of_birth' => '2020-05-22', 'gender' => 'female', 'status' => 'active', 'enrollment_status' => 'enrolled'],
            ['admission_no' => 'ADM2026/006', 'first_name' => 'Juma', 'last_name' => 'Abdalla', 'date_of_birth' => '2019-10-30', 'gender' => 'male', 'status' => 'active', 'enrollment_status' => 'enrolled'],

            // Grade 1 (Lower Primary)
            ['admission_no' => 'ADM2026/007', 'first_name' => 'Nyambura', 'last_name' => 'Njoroge', 'date_of_birth' => '2019-03-02', 'gender' => 'female', 'status' => 'active', 'enrollment_status' => 'enrolled'],
            ['admission_no' => 'ADM2026/008', 'first_name' => 'Brian', 'last_name' => 'Mwangi', 'date_of_birth' => '2019-07-18', 'gender' => 'male', 'status' => 'active', 'enrollment_status' => 'enrolled'],
            ['admission_no' => 'ADM2026/009', 'first_name' => 'Achieng', 'last_name' => 'Omondi', 'date_of_birth' => '2018-12-09', 'gender' => 'female', 'status' => 'active', 'enrollment_status' => 'enrolled'],

            // Grade 2
            ['admission_no' => 'ADM2026/010', 'first_name' => 'Kevin', 'last_name' => 'Kilonzo', 'date_of_birth' => '2018-04-26', 'gender' => 'male', 'status' => 'active', 'enrollment_status' => 'enrolled'],
            ['admission_no' => 'ADM2026/011', 'first_name' => 'Naliaka', 'last_name' => 'Wekesa', 'date_of_birth' => '2018-09-11', 'gender' => 'female', 'status' => 'active', 'enrollment_status' => 'enrolled'],
            ['admission_no' => 'ADM2026/012', 'first_name' => 'Hamisi', 'last_name' => 'Mwinyi', 'date_of_birth' => '2018-02-17', 'gender' => 'male', 'status' => 'active', 'enrollment_status' => 'enrolled'],

            // Grade 3
            ['admission_no' => 'ADM2026/013', 'first_name' => 'Chebet', 'last_name' => 'Kimutai', 'date_of_birth' => '2017-06-01', 'gender' => 'female', 'status' => 'active', 'enrollment_status' => 'enrolled'],
            ['admission_no' => 'ADM2026/014', 'first_name' => 'Samuel', 'last_name' => 'Mutinda', 'date_of_birth' => '2017-11-08', 'gender' => 'male', 'status' => 'active', 'enrollment_status' => 'enrolled'],
            ['admission_no' => 'ADM2026/015', 'first_name' => 'Rehema', 'last_name' => 'Salim', 'date_of_birth' => '2017-01-29', 'gender' => 'female', 'status' => 'active', 'enrollment_status' => 'enrolled'],

            // Grade 4 (Upper Primary)
            ['admission_no' => 'ADM2026/016', 'first_name' => 'Victor', 'last_name' => 'Musyoka', 'date_of_birth' => '2016-05-13', 'gender' => 'male', 'status' => 'active', 'enrollment_status' => 'enrolled'],
            ['admission_no' => 'ADM2026/017', 'first_name' => 'Gathoni', 'last_name' => 'Maina', 'date_of_birth' => '2016-10-24', 'gender' => 'female', 'status' => 'active', 'enrollment_status' => 'enrolled'],
            ['admission_no' => 'ADM2026/018', 'first_name' => 'Ali', 'last_name' => 'Omar', 'date_of_birth' => '2016-03-07', 'gender' => 'male', 'status' => 'active', 'enrollment_status' => 'enrolled'],

            // Grade 5
            ['admission_no' => 'ADM2026/019', 'first_name' => 'Faith', 'last_name' => 'Nyakundi', 'date_of_birth' => '2015-08-19', 'gender' => 'female', 'status' => 'active', 'enrollment_status' => 'enrolled'],
            ['admission_no' => 'ADM2026/020', 'first_name' => 'Dennis', 'last_name' => 'Otieno', 'date_of_birth' => '2015-12-03', 'gender' => 'male', 'status' => 'active', 'enrollment_status' => 'enrolled'],
            ['admission_no' => 'ADM2026/021', 'first_name' => 'Zainab', 'last_name' => 'Hamisi', 'date_of_birth' => '2015-04-27', 'gender' => 'female', 'status' => 'active', 'enrollment_status' => 'enrolled'],

            // Grade 6 (KPSEA year)
            ['admission_no' => 'ADM2026/022', 'first_name' => 'Emmanuel', 'last_name' => 'Kiptoo', 'date_of_birth' => '2014-07-16', 'gender' => 'male', 'status' => 'active', 'enrollment_status' => 'enrolled'],
            ['admission_no' => 'ADM2026/023', 'first_name' => 'Njeri', 'last_name' => 'Kathure', 'date_of_birth' => '2014-01-05', 'gender' => 'female', 'status' => 'active', 'enrollment_status' => 'enrolled'],

            // Grade 7 (Junior School)
            ['admission_no' => 'ADM2026/024', 'first_name' => 'Moses', 'last_name' => 'Ochieng', 'date_of_birth' => '2013-09-21', 'gender' => 'male', 'status' => 'active', 'enrollment_status' => 'enrolled', 'is_hosteller' => true],
            ['admission_no' => 'ADM2026/025', 'first_name' => 'Esther', 'last_name' => 'Wambui', 'date_of_birth' => '2013-02-11', 'gender' => 'female', 'status' => 'active', 'enrollment_status' => 'enrolled', 'uses_transport' => true],

            // Grade 8
            ['admission_no' => 'ADM2026/026', 'first_name' => 'Peter', 'last_name' => 'Njenga', 'date_of_birth' => '2012-06-08', 'gender' => 'male', 'status' => 'active', 'enrollment_status' => 'enrolled', 'is_hosteller' => true],
            ['admission_no' => 'ADM2026/027', 'first_name' => 'Mercy', 'last_name' => 'Adhiambo', 'date_of_birth' => '2012-11-30', 'gender' => 'female', 'status' => 'active', 'enrollment_status' => 'enrolled', 'uses_transport' => true],

            // Grade 9 (JSS assessment year)
            ['admission_no' => 'ADM2026/028', 'first_name' => 'Daniel', 'last_name' => 'Kioko', 'date_of_birth' => '2011-05-22', 'gender' => 'male', 'status' => 'active', 'enrollment_status' => 'enrolled', 'is_hosteller' => true],
            ['admission_no' => 'ADM2026/029', 'first_name' => 'Beatrice', 'last_name' => 'Chepkoech', 'date_of_birth' => '2011-10-14', 'gender' => 'female', 'status' => 'active', 'enrollment_status' => 'enrolled', 'uses_transport' => true],

            // Grade 10 (Senior School)
            ['admission_no' => 'ADM2026/030', 'first_name' => 'George', 'last_name' => 'Mburu', 'date_of_birth' => '2010-04-09', 'gender' => 'male', 'status' => 'active', 'enrollment_status' => 'enrolled', 'is_hosteller' => true],
            ['admission_no' => 'ADM2026/031', 'first_name' => 'Ruth', 'last_name' => 'Nyaboke', 'date_of_birth' => '2010-09-27', 'gender' => 'female', 'status' => 'active', 'enrollment_status' => 'enrolled', 'uses_transport' => true],

            // Grade 11
            ['admission_no' => 'ADM2026/032', 'first_name' => 'James', 'last_name' => 'Mwangi', 'date_of_birth' => '2009-03-18', 'gender' => 'male', 'status' => 'active', 'enrollment_status' => 'enrolled', 'is_hosteller' => true],
            ['admission_no' => 'ADM2026/033', 'first_name' => 'Lucy', 'last_name' => 'Wanjiru', 'date_of_birth' => '2009-08-02', 'gender' => 'female', 'status' => 'active', 'enrollment_status' => 'enrolled', 'uses_transport' => true],

            // Grade 12 (KCSE year)
            ['admission_no' => 'ADM2026/034', 'first_name' => 'Michael', 'last_name' => 'Otieno', 'date_of_birth' => '2008-01-20', 'gender' => 'male', 'status' => 'active', 'enrollment_status' => 'enrolled', 'is_hosteller' => true],
            ['admission_no' => 'ADM2026/035', 'first_name' => 'Ann', 'last_name' => 'Moraa', 'date_of_birth' => '2008-06-12', 'gender' => 'female', 'status' => 'active', 'enrollment_status' => 'enrolled', 'uses_transport' => true],
            ['admission_no' => 'ADM2026/036', 'first_name' => 'Felix', 'last_name' => 'Njoroge', 'date_of_birth' => '2008-11-05', 'gender' => 'male', 'status' => 'active', 'enrollment_status' => 'enrolled', 'is_hosteller' => true],

            // Alumni + transferred (historical records)
            ['admission_no' => 'ADM2025/101', 'first_name' => 'Collins', 'last_name' => 'Wekesa', 'date_of_birth' => '2007-04-15', 'gender' => 'male', 'status' => 'alumni', 'enrollment_status' => 'graduated', 'graduation_date' => '2025-11-21'],
            ['admission_no' => 'ADM2025/102', 'first_name' => 'Grace', 'last_name' => 'Akinyi', 'date_of_birth' => '2007-09-08', 'gender' => 'female', 'status' => 'alumni', 'enrollment_status' => 'graduated', 'graduation_date' => '2025-11-21'],
            ['admission_no' => 'ADM2025/103', 'first_name' => 'Noah', 'last_name' => 'Wanyonyi', 'date_of_birth' => '2013-12-20', 'gender' => 'male', 'status' => 'transferred', 'enrollment_status' => 'transferred', 'transfer_date' => '2026-07-15', 'transfer_reason' => 'Family relocation to Mombasa'],
            ['admission_no' => 'ADM2024/201', 'first_name' => 'Ivy', 'last_name' => 'Mwikali', 'date_of_birth' => '2014-07-03', 'gender' => 'female', 'status' => 'inactive', 'enrollment_status' => 'dropped_out', 'leaving_reason' => 'Withdrew to a day school'],
        ];

        $counties = [
            'Nairobi', 'Kiambu', 'Mombasa', 'Kisumu', 'Nakuru', 'Machakos', 'Kajiado', 'Uasin Gishu',
            'Kilifi', 'Muranga', 'Makueni', 'Nyeri', 'Bungoma', 'Kisii', 'Siaya', 'Kakamega', 'Taita Taveta',
        ];

        $surnamesByCounty = [
            'Nairobi' => 'Wanjiru', 'Kiambu' => 'Kamau', 'Mombasa' => 'Omar', 'Kisumu' => 'Odhiambo',
            'Nakuru' => 'Wambui', 'Machakos' => 'Mutua', 'Kajiado' => 'Lesian', 'Uasin Gishu' => 'Kipchoge',
            'Kilifi' => 'Mwinyi', 'Muranga' => 'Njoroge', 'Makueni' => 'Munyao', 'Nyeri' => 'Maina',
            'Bungoma' => 'Wekesa', 'Kisii' => 'Mose', 'Siaya' => 'Ochieng', 'Kakamega' => 'Wanyonyi', 'Taita Taveta' => 'Mwakida',
        ];

        $religions = ['Christian', 'Muslim', 'Hindu', 'Other'];
        $bloodGroups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];

        $idx = 0;
        foreach ($students as $data) {
            $county = $counties[$idx % count($counties)];
            $gender = $data['gender'];

            $data = array_merge([
                'middle_name' => null,
                'nemis_number' => '4' . str_pad((string)($idx + 5100), 7, '0', STR_PAD_LEFT),
                'upi_number' => 'UPI' . str_pad((string)($idx + 101), 8, '0', STR_PAD_LEFT),
                'birth_certificate_no' => 'BC' . str_pad((string)($idx + 30001), 8, '0', STR_PAD_LEFT),
                'nationality' => 'Kenyan',
                'religion' => $religions[$idx % count($religions)],
                'blood_group' => $bloodGroups[$idx % count($bloodGroups)],
                'address' => 'P.O. Box ' . (4500 + $idx) . ', ' . $county,
                'city' => $county === 'Nairobi' ? 'Nairobi' : $county,
                'county' => $county,
                'sub_county' => $this->subCountyFor($county, $idx),
                'country' => 'Kenya',
                'postal_code' => $this->postalCodeFor($county),
                'phone' => $this->phone(712, $idx),
                'emergency_contact' => $this->phone(723, $idx),
                'emergency_contact_name' => 'Parent/Guardian of ' . $data['first_name'],
                'emergency_contact_relationship' => 'Guardian',
                'admission_date' => $data['enrollment_status'] === 'enrolled' ? '2026-01-05' : '2025-01-06',
                'previous_school' => 'St. ' . $surnamesByCounty[$county] . ' Primary School',
                'previous_class' => ucwords(strtolower($county)) . ' Public School',
                'photo_url' => null,
                'student_category' => $idx % 6 === 0 ? 'orphan' : ($idx % 9 === 0 ? 'partial_orphan' : 'regular'),
                'education_system' => 'CBC',
                'is_scholarship_holder' => $idx % 8 === 0,
                'scholarship_details' => $idx % 8 === 0 ? 'Shujaa Academy Bursary Fund' : null,
                'medical_conditions' => $idx % 7 === 0 ? 'Mild asthma' : null,
                'allergies' => $idx % 7 === 3 ? 'Peanuts' : null,
                'medications' => null,
                'doctor_name' => 'Dr. ' . $surnamesByCounty[$county] . ' Clinic, ' . $county,
                'doctor_phone' => $this->phone(735, $idx),
                'is_active' => true,
            ], $data);

            Student::firstOrCreate(
                ['admission_no' => $data['admission_no']],
                $data
            );
            $idx++;
        }
    }

    private function phone(int $prefix, int $idx): string
    {
        $suffix = str_pad((string)(100000 + $idx * 17 % 900000), 6, '0', STR_PAD_LEFT);
        return '0' . $prefix . $suffix;
    }

    private function subCountyFor(string $county, int $idx): string
    {
        $subCounties = [
            'Nairobi' => ['Westlands', 'Kasarani', 'Dagoretti', 'Langata', 'Embakasi'],
            'Kiambu' => ['Thika', 'Ruiru', 'Kiambu', 'Ruiru East'],
            'Mombasa' => ['Nyali', 'Kisauni', 'Likoni', 'Mvita'],
            'Kisumu' => ['Kisumu Central', 'Winam', 'Nyando'],
            'Nakuru' => ['Nakuru Town East', 'Nakuru Town West', 'Gilgil'],
            'Machakos' => ['Mavoko', 'Machakos Town', 'Kathiani'],
            'Kajiado' => ['Kitengela', 'Kajiado Town', 'Ngong'],
            'Uasin Gishu' => ['Eldoret East', 'Eldoret West', 'Kapseret'],
            'Kilifi' => ['Kilifi North', 'Kilifi South', 'Magarini'],
            'Muranga' => ['Muranga Town', 'Kandara', 'Gatanga'],
            'Makueni' => ['Makueni', 'Kibwezi', 'Mbooni'],
            'Nyeri' => ['Nyeri Town', 'Mukurweini', 'Mathira'],
            'Bungoma' => ['Bungoma Town', 'Webuye', 'Kanduyi'],
            'Kisii' => ['Kisii Central', 'Kitutu Chache', 'Nyaribari'],
            'Siaya' => ['Bondo', 'Gem', 'Ugenya'],
            'Kakamega' => ['Kakamega Central', 'Lurambi', 'Ikolomani'],
            'Taita Taveta' => ['Voi', 'Taveta', 'Mwambongu'],
        ];
        $list = $subCounties[$county] ?? ['Central', 'Ruiru'];
        return $list[$idx % count($list)];
    }

    private function postalCodeFor(string $county): string
    {
        $codes = [
            'Nairobi' => '00100', 'Kiambu' => '01000', 'Mombasa' => '80100', 'Kisumu' => '40100',
            'Nakuru' => '20100', 'Machakos' => '90100', 'Kajiado' => '00208', 'Uasin Gishu' => '30100',
            'Kilifi' => '80109', 'Muranga' => '10200', 'Makueni' => '90300', 'Nyeri' => '10100',
            'Bungoma' => '50200', 'Kisii' => '40200', 'Siaya' => '40600', 'Kakamega' => '50100', 'Taita Taveta' => '80300',
        ];
        return $codes[$county] ?? '00100';
    }
}
