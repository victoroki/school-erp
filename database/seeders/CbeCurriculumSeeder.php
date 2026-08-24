<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\CbcLearningArea;
use App\Models\CbcStrand;
use App\Models\CbcSubStrand;
use App\Models\AuditTrail;

/**
 * Seeds the official Kenyan CBE (Competency Based Education) curriculum
 * structure — learning areas, strands and sub-strands per level.
 *
 * Junior School (Grades 7-9): the nine compulsory learning areas from the
 * January 2024 KICD rationalisation, each with its KICD design strands.
 * Upper Primary (4-6) and Lower Primary (1-3) ship their core areas too so
 * full primary schools can adopt the system as-is.
 *
 * Idempotent: safe to re-run (updateOrCreate by name + level).
 */
class CbeCurriculumSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->curriculum() as $level => $areas) {
            foreach ($areas as $area) {
                $learningArea = CbcLearningArea::updateOrCreate(
                    ['name' => $area['name'], 'level' => $level],
                    [
                        'code'        => $area['code'] ?? null,
                        'description' => $area['description'] ?? null,
                        'status'      => true,
                    ]
                );

                foreach ($area['strands'] ?? [] as $strandIndex => $strandData) {
                    $strandName = is_string($strandData) ? $strandData : $strandData['name'];
                    $subStrands = is_string($strandData) ? [] : ($strandData['sub_strands'] ?? []);

                    $strand = CbcStrand::updateOrCreate(
                        ['learning_area_id' => $learningArea->id, 'name' => $strandName],
                        ['description' => 'Strand ' . ($strandIndex + 1)]
                    );

                    foreach ($subStrands as $subIndex => $subStrandName) {
                        CbcSubStrand::updateOrCreate(
                            ['strand_id' => $strand->id, 'name' => $subStrandName],
                            ['description' => null]
                        );
                    }
                }
            }
        }

        AuditTrail::log('CBE Curriculum', 'SEED', null, null, [
            'levels' => array_keys($this->curriculum()),
        ]);
    }

    private function curriculum(): array
    {
        return [

            // ─────────────────────────────────────────────────────────
            // JUNIOR SCHOOL (Grades 7–9)
            // ─────────────────────────────────────────────────────────
            'Junior School' => [
                [
                    'name' => 'English', 'code' => 'ENG',
                    'description' => 'Listening & Speaking, Reading, Writing and Language Structures',
                    'strands' => [
                        'Listening and Speaking',
                        'Reading',
                        'Writing',
                        ['name' => 'Language Structures and Grammar', 'sub_strands' => ['Word Classes', 'Sentence Construction']],
                    ],
                ],
                [
                    'name' => 'Kiswahili / Kenya Sign Language', 'code' => 'KSL',
                    'description' => 'Kusikiliza na Kuzungumza, Kusoma, Kuandika na Sarufi (au KSL)',
                    'strands' => [
                        'Kusikiliza na Kuzungumza',
                        'Kusoma',
                        'Kuandika',
                        'Sarufi',
                    ],
                ],
                [
                    'name' => 'Mathematics', 'code' => 'MAT',
                    'description' => 'Numbers, Algebra, Measurement, Geometry and Data handling',
                    'strands' => [
                        'Numbers',
                        'Algebra',
                        ['name' => 'Measurements', 'sub_strands' => ['Money', 'Perimeter, Area and Volume', 'Time, Distance and Speed']],
                        'Geometry',
                        ['name' => 'Data Handling and Probability', 'sub_strands' => ['Data Collection and Representation', 'Probability']],
                    ],
                ],
                [
                    'name' => 'Integrated Science / Health Education', 'code' => 'ISC',
                    'description' => 'Scientific concepts across biology, chemistry and physics with health education',
                    'strands' => [
                        'Introduction to Integrated Science',
                        'Mixtures, Elements and Compounds',
                        'Living Things and Their Environment',
                        ['name' => 'Force and Energy', 'sub_strands' => ['Electricity and Magnetism', 'Light and Sound']],
                        'Health Education',
                    ],
                ],
                [
                    'name' => 'Pre-Technical Studies', 'code' => 'PTS',
                    'description' => 'Materials, technical drawing, ICT, entrepreneurship and safety',
                    'strands' => [
                        'Foundations of Pre-Technical Studies',
                        ['name' => 'Materials for Production', 'sub_strands' => ['Wood', 'Metals', 'Plastics']],
                        ['name' => 'Technical Drawing', 'sub_strands' => ['Free-hand Sketching', 'Geometric Construction']],
                        'ICT and Digital Literacy',
                        ['name' => 'Entrepreneurship and Financial Literacy', 'sub_strands' => ['Business Ideas', 'Bookkeeping']],
                        'Safety in the Workplace',
                    ],
                ],
                [
                    'name' => 'Social Studies / Life Skills Education', 'code' => 'SST',
                    'description' => 'History, Geography, Governance, Citizenship and Life Skills',
                    'strands' => [
                        ['name' => 'Natural and Historic Built Environments', 'sub_strands' => ['Map Work', 'Physical Features']],
                        ['name' => 'People and Population', 'sub_strands' => ['Population Growth', 'Migration']],
                        'Governance, Citizenship and Human Rights',
                        'Life Skills Education',
                    ],
                ],
                [
                    'name' => 'Religious Education (CRE/IRE/HRE)', 'code' => 'CRE',
                    'description' => 'Christian, Islamic or Hindu Religious Education',
                    'strands' => [
                        'The Bible / The Holy Books',
                        'Creation and the Will of God',
                        'Faith and Worship',
                        'Christian Living / Moral Values',
                    ],
                ],
                [
                    'name' => 'Agriculture / Nutrition and Food Security', 'code' => 'AGR',
                    'description' => 'Crop and livestock production, food processes, nutrition and hygiene',
                    'strands' => [
                        'Introduction to Agriculture and Technology',
                        ['name' => 'Crop Production', 'sub_strands' => ['Land Preparation', 'Planting and Care', 'Harvesting']],
                        'Livestock Production',
                        ['name' => 'Food and Nutrition', 'sub_strands' => ['Food Nutrients', 'Food Preparation and Preservation']],
                        'Hygiene and Sanitation',
                    ],
                ],
                [
                    'name' => 'Creative Arts and Sports', 'code' => 'CAS',
                    'description' => 'Visual arts, performing arts, music and physical education',
                    'strands' => [
                        ['name' => 'Visual Arts', 'sub_strands' => ['Drawing', 'Painting', 'Modelling']],
                        ['name' => 'Performing Arts', 'sub_strands' => ['Music', 'Dance', 'Drama']],
                        ['name' => 'Sports and Physical Education', 'sub_strands' => ['Athletics', 'Ball Games', 'Gymnastics']],
                    ],
                ],
            ],

            // ─────────────────────────────────────────────────────────
            // UPPER PRIMARY (Grades 4–6)
            // ─────────────────────────────────────────────────────────
            'Upper Primary' => [
                ['name' => 'English', 'code' => 'ENG', 'strands' => ['Listening and Speaking', 'Reading', 'Writing', 'Grammar']],
                ['name' => 'Kiswahili / Kenya Sign Language', 'code' => 'KSL', 'strands' => ['Kusikiliza na Kuzungumza', 'Kusoma', 'Kuandika', 'Sarufi']],
                ['name' => 'Mathematics', 'code' => 'MAT', 'strands' => ['Numbers', 'Measurement', 'Geometry', 'Data Handling']],
                ['name' => 'Science and Technology', 'code' => 'SNT', 'strands' => ['Living Things', 'Matter and Materials', 'Force, Motion and Energy', 'Digital Technology']],
                ['name' => 'Social Studies', 'code' => 'SST', 'strands' => ['History and Citizenship', 'Geography', 'Civic Education']],
                ['name' => 'Religious Education (CRE/IRE/HRE)', 'code' => 'CRE', 'strands' => ['Sacred Writings', 'Worship', 'Moral Values']],
                ['name' => 'Agriculture', 'code' => 'AGR', 'strands' => ['Crop Production', 'Animal Care', 'Conservation']],
                ['name' => 'Creative Arts', 'code' => 'CAT', 'strands' => ['Art and Craft', 'Music', 'Movement and Games']],
            ],

            // ─────────────────────────────────────────────────────────
            // LOWER PRIMARY (Grades 1–3)
            // ─────────────────────────────────────────────────────────
            'Lower Primary' => [
                ['name' => 'Literacy — English Activities', 'code' => 'ENG', 'strands' => ['Listening and Speaking', 'Reading', 'Writing']],
                ['name' => 'Kiswahili Activities', 'code' => 'KIS', 'strands' => ['Kusikiliza na Kutamka', 'Kusoma', 'Kuandika']],
                ['name' => 'Numeracy — Mathematics Activities', 'code' => 'NUM', 'strands' => ['Numbers', 'Operations', 'Measurement and Shapes']],
                ['name' => 'Environmental Activities', 'code' => 'ENV', 'strands' => ['Myself and My Family', 'Our Environment', 'Our Community']],
                ['name' => 'Hygiene and Nutrition Activities', 'code' => 'HYG', 'strands' => ['Personal Hygiene', 'Food and Nutrition', 'Sanitation']],
                ['name' => 'Movement and Creative Activities', 'code' => 'MOV', 'strands' => ['Movement Activities', 'Art and Craft', 'Music and Rhythm']],
                ['name' => 'Religious Education Activities', 'code' => 'CRE', 'strands' => ['God’s Creation', 'Worship and Prayer', 'Good Behaviour']],
            ],

            // ─────────────────────────────────────────────────────────
            // PRE-PRIMARY (PP1–PP2)
            // ─────────────────────────────────────────────────────────
            'Pre-Primary' => [
                ['name' => 'Language Activities', 'code' => 'LANG', 'strands' => ['Listening and Speaking', 'Pre-reading', 'Pre-writing']],
                ['name' => 'Mathematical Activities', 'code' => 'MATH', 'strands' => ['Number Work', 'Sorting and Matching', 'Shapes']],
                ['name' => 'Environmental Activities', 'code' => 'ENV', 'strands' => ['Exploring the Environment', 'Care for Nature']],
                ['name' => 'Psychomotor and Creative Activities', 'code' => 'PSY', 'strands' => ['Body Movement', 'Creative Expression']],
                ['name' => 'Religious Education Activities', 'code' => 'CRE', 'strands' => ['Simple Bible Stories', 'Songs and Prayers']],
            ],
        ];
    }
}
