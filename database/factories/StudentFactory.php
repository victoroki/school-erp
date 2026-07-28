<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        static $admission = 1000;
        return [
            'admission_no'  => 'ADM-' . ($admission++),
            'first_name'    => $this->faker->firstName,
            'last_name'     => $this->faker->lastName,
            'date_of_birth' => $this->faker->date('Y-m-d', '2012-01-01'),
            'gender'        => $this->faker->randomElement(['male', 'female']),
            'admission_date'=> now(),
            'is_active'     => true,
            'status'        => 'active',
        ];
    }
}
