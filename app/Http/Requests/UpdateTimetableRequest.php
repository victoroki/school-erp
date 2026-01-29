<?php

namespace App\Http\Requests;

use App\Models\Timetable;
use App\Models\TeacherSubject;
use App\Models\ClassSubject;
use App\Models\ClassSection;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTimetableRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = Timetable::$rules;

        return $rules;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $classSectionId = $this->input('class_section_id');
            $academicYearId = $this->input('academic_year_id');
            $dayOfWeek = $this->input('day_of_week');
            $periodId = $this->input('period_id');
            $teacherId = $this->input('teacher_id');
            $classroomId = $this->input('classroom_id');
            $subjectId = $this->input('subject_id');
            $timetableId = $this->route('timetable');

            if (
                !$classSectionId ||
                !$academicYearId ||
                !$dayOfWeek ||
                !$periodId ||
                !$teacherId ||
                !$classroomId ||
                !$subjectId
            ) {
                return;
            }

            $classSlotExists = Timetable::where('class_section_id', $classSectionId)
                ->where('academic_year_id', $academicYearId)
                ->where('day_of_week', $dayOfWeek)
                ->where('period_id', $periodId)
                ->when($timetableId, function ($query, $timetableId) {
                    return $query->where('timetable_id', '!=', $timetableId);
                })
                ->exists();

            if ($classSlotExists) {
                $validator->errors()->add(
                    'period_id',
                    'This class and section already has a lesson in the selected period on this day.'
                );
            }

            $teacherConflict = Timetable::where('teacher_id', $teacherId)
                ->where('academic_year_id', $academicYearId)
                ->where('day_of_week', $dayOfWeek)
                ->where('period_id', $periodId)
                ->when($timetableId, function ($query, $timetableId) {
                    return $query->where('timetable_id', '!=', $timetableId);
                })
                ->exists();

            if ($teacherConflict) {
                $validator->errors()->add(
                    'teacher_id',
                    'The selected teacher is already scheduled for another class at this time.'
                );
            }

            $classroomConflict = Timetable::where('classroom_id', $classroomId)
                ->where('academic_year_id', $academicYearId)
                ->where('day_of_week', $dayOfWeek)
                ->where('period_id', $periodId)
                ->when($timetableId, function ($query, $timetableId) {
                    return $query->where('timetable_id', '!=', $timetableId);
                })
                ->exists();

            if ($classroomConflict) {
                $validator->errors()->add(
                    'classroom_id',
                    'The selected classroom is already in use at this time.'
                );
            }

            $teacherSubjectExists = TeacherSubject::where('staff_id', $teacherId)
                ->where('subject_id', $subjectId)
                ->where('class_section_id', $classSectionId)
                ->where('academic_year_id', $academicYearId)
                ->exists();

            if (!$teacherSubjectExists) {
                $validator->errors()->add(
                    'teacher_id',
                    'The selected teacher is not assigned to teach this subject for the chosen class and academic year.'
                );
            }

            $classSection = ClassSection::find($classSectionId);
            if ($classSection) {
                $classId = $classSection->class_id;

                $classSubjectExists = ClassSubject::where('class_id', $classId)
                    ->where('subject_id', $subjectId)
                    ->where('academic_year_id', $academicYearId)
                    ->exists();

                if (!$classSubjectExists) {
                    $validator->errors()->add(
                        'subject_id',
                        'The selected subject is not configured for this class in the chosen academic year.'
                    );
                }
            }
        });
    }
}
