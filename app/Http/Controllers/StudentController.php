<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\StudentRepository;
use Illuminate\Http\Request;
use Flash;

class StudentController extends AppBaseController
{
    /** @var StudentRepository $studentRepository*/
    private $studentRepository;

    public function __construct(StudentRepository $studentRepo)
    {
        $this->studentRepository = $studentRepo;
    }

    /**
     * Display a listing of the Student.
     */
    public function index(Request $request)
    {
        $students = $this->studentRepository->paginate(10);

        return view('students.index')
            ->with('students', $students);
    }

    /**
     * Show the form for creating a new Student.
     */
    public function create()
    {
        return view('students.create');
    }

    /**
     * Store a newly created Student in storage.
     */
    public function store(CreateStudentRequest $request)
    {
        $input = $request->all();

        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $photoName = time() . '.' . $photo->getClientOriginalExtension();
            $photo->move(public_path('students/photos'), $photoName);
            $input['photo_url'] = 'students/photos/' . $photoName;
        }

        $student = $this->studentRepository->create($input);

        Flash::success('Student saved successfully.');

        return redirect(route('students.index'));
    }

    /**
     * Display the specified Student.
     */
    public function show($id)
    {
        $student = $this->studentRepository->find($id);

        if (empty($student)) {
            Flash::error('Student not found');

            return redirect(route('students.index'));
        }

        return view('students.show')->with('student', $student);
    }

    /**
     * Show the form for editing the specified Student.
     */
    public function edit($id)
    {
        $student = $this->studentRepository->find($id);

        if (empty($student)) {
            Flash::error('Student not found');

            return redirect(route('students.index'));
        }

        return view('students.edit')->with('student', $student);
    }

    /**
     * Update the specified Student in storage.
     */
    public function update($id, UpdateStudentRequest $request)
    {
        $student = $this->studentRepository->find($id);

        if (empty($student)) {
            Flash::error('Student not found');

            return redirect(route('students.index'));
        }

        $input = $request->all();

        if ($request->hasFile('photo')) {
            // Optional: Delete old photo if it exists
            if ($student->photo_url && file_exists(public_path($student->photo_url))) {
                unlink(public_path($student->photo_url));
            }

            $photo = $request->file('photo');
            $photoName = time() . '.' . $photo->getClientOriginalExtension();
            $photo->move(public_path('students/photos'), $photoName);
            $input['photo_url'] = 'students/photos/' . $photoName;
        }


        $student = $this->studentRepository->update($input, $id);

        Flash::success('Student updated successfully.');

        return redirect(route('students.index'));
    }

    /**
     * Remove the specified Student from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $student = $this->studentRepository->find($id);

        if (empty($student)) {
            Flash::error('Student not found');

            return redirect(route('students.index'));
        }
        
        // Optional: Delete photo when student is deleted
        if ($student->photo_url && file_exists(public_path($student->photo_url))) {
            unlink(public_path($student->photo_url));
        }

        $this->studentRepository->delete($id);

        Flash::success('Student deleted successfully.');

        return redirect(route('students.index'));
    }
}
