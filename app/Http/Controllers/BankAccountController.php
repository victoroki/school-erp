<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateBankAccountRequest;
use App\Http\Requests\UpdateBankAccountRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\BankAccountRepository;
use Illuminate\Http\Request;
use Flash;

class BankAccountController extends AppBaseController
{
    /** @var BankAccountRepository $bankAccountRepository*/
    private $bankAccountRepository;

    public function __construct(BankAccountRepository $bankAccountRepo)
    {
        $this->bankAccountRepository = $bankAccountRepo;
    }

    /**
     * Display a listing of the BankAccount.
     */
    public function index(Request $request)
    {
        $bankAccounts = $this->bankAccountRepository->paginate(10);

        return view('bank_accounts.index')
            ->with('bankAccounts', $bankAccounts);
    }

    /**
     * Show the form for creating a new BankAccount.
     */
    public function create()
    {
        return view('bank_accounts.create');
    }

    /**
     * Store a newly created BankAccount in storage.
     */
    public function store(CreateBankAccountRequest $request)
    {
        $input = $request->all();

        $bankAccount = $this->bankAccountRepository->create($input);

        Flash::success('Bank Account saved successfully.');

        return redirect(route('bankAccounts.index'));
    }

    /**
     * Display the specified BankAccount.
     */
    public function show($id)
    {
        $bankAccount = $this->bankAccountRepository->find($id);

        if (empty($bankAccount)) {
            Flash::error('Bank Account not found');

            return redirect(route('bankAccounts.index'));
        }

        return view('bank_accounts.show')->with('bankAccount', $bankAccount);
    }

    /**
     * Show the form for editing the specified BankAccount.
     */
    public function edit($id)
    {
        $bankAccount = $this->bankAccountRepository->find($id);

        if (empty($bankAccount)) {
            Flash::error('Bank Account not found');

            return redirect(route('bankAccounts.index'));
        }

        return view('bank_accounts.edit')->with('bankAccount', $bankAccount);
    }

    /**
     * Update the specified BankAccount in storage.
     */
    public function update($id, UpdateBankAccountRequest $request)
    {
        $bankAccount = $this->bankAccountRepository->find($id);

        if (empty($bankAccount)) {
            Flash::error('Bank Account not found');

            return redirect(route('bankAccounts.index'));
        }

        $bankAccount = $this->bankAccountRepository->update($request->all(), $id);

        Flash::success('Bank Account updated successfully.');

        return redirect(route('bankAccounts.index'));
    }

    /**
     * Remove the specified BankAccount from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $bankAccount = $this->bankAccountRepository->find($id);

        if (empty($bankAccount)) {
            Flash::error('Bank Account not found');

            return redirect(route('bankAccounts.index'));
        }

        $this->bankAccountRepository->delete($id);

        Flash::success('Bank Account deleted successfully.');

        return redirect(route('bankAccounts.index'));
    }
}
