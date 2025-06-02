<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateParentsRequest;
use App\Http\Requests\UpdateParentsRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\ParentsRepository;
use Illuminate\Http\Request;
use Flash;

class ParentsController extends AppBaseController
{
    /** @var ParentsRepository $parentsRepository*/
    private $parentsRepository;

    public function __construct(ParentsRepository $parentsRepo)
    {
        $this->parentsRepository = $parentsRepo;
    }

    /**
     * Display a listing of the Parents.
     */
    public function index(Request $request)
    {
        $parents = $this->parentsRepository->paginate(10);

        return view('parents.index')
            ->with('parents', $parents);
    }

    /**
     * Show the form for creating a new Parents.
     */
    public function create()
    {
        return view('parents.create');
    }

    /**
     * Store a newly created Parents in storage.
     */
    public function store(CreateParentsRequest $request)
    {
        $input = $request->all();

        $parents = $this->parentsRepository->create($input);

        Flash::success('Parents saved successfully.');

        return redirect(route('parents.index'));
    }

    /**
     * Display the specified Parents.
     */
    public function show($id)
    {
        $parents = $this->parentsRepository->find($id);

        if (empty($parents)) {
            Flash::error('Parents not found');

            return redirect(route('parents.index'));
        }

        return view('parents.show')->with('parents', $parents);
    }

    /**
     * Show the form for editing the specified Parents.
     */
    public function edit($id)
    {
        $parents = $this->parentsRepository->find($id);

        if (empty($parents)) {
            Flash::error('Parents not found');

            return redirect(route('parents.index'));
        }

        return view('parents.edit')->with('parents', $parents);
    }

    /**
     * Update the specified Parents in storage.
     */
    public function update($id, UpdateParentsRequest $request)
    {
        $parents = $this->parentsRepository->find($id);

        if (empty($parents)) {
            Flash::error('Parents not found');

            return redirect(route('parents.index'));
        }

        $parents = $this->parentsRepository->update($request->all(), $id);

        Flash::success('Parents updated successfully.');

        return redirect(route('parents.index'));
    }

    /**
     * Remove the specified Parents from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $parents = $this->parentsRepository->find($id);

        if (empty($parents)) {
            Flash::error('Parents not found');

            return redirect(route('parents.index'));
        }

        $this->parentsRepository->delete($id);

        Flash::success('Parents deleted successfully.');

        return redirect(route('parents.index'));
    }
}
