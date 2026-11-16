<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateMessageRequest;
use App\Http\Requests\UpdateMessageRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\MessageRepository;
use App\Models\Message;
use App\Models\User;
use App\Models\AuditTrail;
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
        $this->middleware('can:communication.view')->only(['index', 'show']);
        $this->middleware('can:communication.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    private function getDropdownData()
    {
        return [
            'users' => User::pluck('name', 'id')
        ];
    }

    /**
     * Display a listing of the Message.
     *
     * PHASE 5 privacy fix: the repository paginate() exposed EVERY message on
     * the platform to anyone reaching this list. School leadership
     * (communication.manage — the console roles) keep the full audit history;
     * everyone else sees only their own sent/received messages — a DM between
     * two staff members is no longer platform-readable.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Message::with(['sender', 'receiver']);

        if (!$user->hasPermission('communication.manage')) {
            $query->where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)->orWhere('receiver_id', $user->id);
            });
        }

        $messages = $query->orderBy('created_at', 'desc')->paginate(10);

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

        AuditTrail::log('Message', 'CREATE', $message->message_id, null, $message->toArray());

        Flash::success('Message sent successfully.');

        return redirect(route('messages.index'));
    }

    /**
     * Display the specified Message.
     *
     * PHASE 5: participants-or-console only (mirrors the index rule) — an
     * IDOR via message_id no longer leaks other people's DMs.
     */
    public function show($id)
    {
        $message = $this->messageRepository->find($id);

        if (empty($message)) {
            Flash::error('Message not found');

            return redirect(route('messages.index'));
        }

        $user = request()->user();
        $participant = $message->sender_id === $user->id || $message->receiver_id === $user->id;
        abort_unless($participant || $user->hasPermission('communication.manage'), 403);

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

        $oldData = $message->toArray();
        $message = $this->messageRepository->update($request->all(), $id);

        AuditTrail::log('Message', 'UPDATE', $message->message_id, $oldData, $message->toArray());

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

        $oldData = $message->toArray();
        $this->messageRepository->delete($id);

        AuditTrail::log('Message', 'DELETE', $id, $oldData, null);

        Flash::success('Message deleted successfully.');

        return redirect(route('messages.index'));
    }
}