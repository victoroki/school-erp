<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateMessageRequest;
use App\Http\Requests\UpdateMessageRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\MessageRepository;
use App\Models\User;
use Illuminate\Http\Request;
use Flash;
use Illuminate\Support\Arr;

class MessageController extends AppBaseController
{
    /** @var MessageRepository $messageRepository*/
    private $messageRepository;

    public function __construct(MessageRepository $messageRepo)
    {
        $this->messageRepository = $messageRepo;
    }

    private function getDropdownData()
    {
        return [
            'users' => User::pluck('name', 'id')
        ];
    }

    /**
     * Display a listing of the Message.
     */
    public function index(Request $request)
    {
        $messages = $this->messageRepository->paginate(10);

        return view('messages.index')
            ->with('messages', $messages);
    }

    /**
     * Show the form for creating a new Message.
     */
    public function create()
    {
        $dropdownData = $this->getDropdownData();
        return view('messages.create', $dropdownData);
    }

    /**
     * Store a newly created Message in storage.
     */
    public function store(CreateMessageRequest $request)
    {
        $input = $request->all();

        $message = $this->messageRepository->create($input);

        Flash::success('Message sent successfully.');

        return redirect(route('messages.index'));
    }

    /**
     * Display the specified Message.
     */
    public function show($id)
    {
        $message = $this->messageRepository->find($id);

        if (empty($message)) {
            Flash::error('Message not found');

            return redirect(route('messages.index'));
        }

        return view('messages.show')->with('message', $message);
    }

    /**
     * Show the form for editing the specified Message.
     */
    public function edit($id)
    {
        $message = $this->messageRepository->find($id);

        if (empty($message)) {
            Flash::error('Message not found');

            return redirect(route('messages.index'));
        }

        $dropdownData = $this->getDropdownData();
        return view('messages.edit', array_merge(['message' => $message], $dropdownData));
    }

    /**
     * Update the specified Message in storage.
     */
    public function update($id, UpdateMessageRequest $request)
    {
        $message = $this->messageRepository->find($id);

        if (empty($message)) {
            Flash::error('Message not found');

            return redirect(route('messages.index'));
        }

        $message = $this->messageRepository->update($request->all(), $id);

        Flash::success('Message updated successfully.');

        return redirect(route('messages.index'));
    }

    /**
     * Remove the specified Message from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $message = $this->messageRepository->find($id);

        if (empty($message)) {
            Flash::error('Message not found');

            return redirect(route('messages.index'));
        }

        $this->messageRepository->delete($id);

        Flash::success('Message deleted successfully.');

        return redirect(route('messages.index'));
    }
}