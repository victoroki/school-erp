<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateNotificationRequest;
use App\Http\Requests\UpdateNotificationRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\NotificationRepository;
use App\Models\User;
use Illuminate\Http\Request;
use Flash;
use Illuminate\Support\Arr;

class NotificationController extends AppBaseController
{
    /** @var NotificationRepository $notificationRepository*/
    private $notificationRepository;

    public function __construct(NotificationRepository $notificationRepo)
    {
        $this->notificationRepository = $notificationRepo;
    }

    private function getDropdownData()
    {
        return [
            'users' => User::pluck('name', 'id')
        ];
    }

    /**
     * Display a listing of the Notification.
     */
    public function index(Request $request)
    {
        $notifications = $this->notificationRepository->paginate(10);

        return view('notifications.index')
            ->with('notifications', $notifications);
    }

    /**
     * Show the form for creating a new Notification.
     */
    public function create()
    {
        $dropdownData = $this->getDropdownData();
        return view('notifications.create', $dropdownData);
    }

    /**
     * Store a newly created Notification in storage.
     */
    public function store(CreateNotificationRequest $request)
    {
        $input = $request->all();

        $notification = $this->notificationRepository->create($input);

        Flash::success('Notification saved successfully.');

        return redirect(route('notifications.index'));
    }

    /**
     * Display the specified Notification.
     */
    public function show($id)
    {
        $notification = $this->notificationRepository->find($id);

        if (empty($notification)) {
            Flash::error('Notification not found');

            return redirect(route('notifications.index'));
        }

        return view('notifications.show')->with('notification', $notification);
    }

    /**
     * Show the form for editing the specified Notification.
     */
    public function edit($id)
    {
        $notification = $this->notificationRepository->find($id);

        if (empty($notification)) {
            Flash::error('Notification not found');

            return redirect(route('notifications.index'));
        }

        $dropdownData = $this->getDropdownData();
        return view('notifications.edit', array_merge(['notification' => $notification], $dropdownData));
    }

    /**
     * Update the specified Notification in storage.
     */
    public function update($id, UpdateNotificationRequest $request)
    {
        $notification = $this->notificationRepository->find($id);

        if (empty($notification)) {
            Flash::error('Notification not found');

            return redirect(route('notifications.index'));
        }

        $notification = $this->notificationRepository->update($request->all(), $id);

        Flash::success('Notification updated successfully.');

        return redirect(route('notifications.index'));
    }

    /**
     * Remove the specified Notification from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $notification = $this->notificationRepository->find($id);

        if (empty($notification)) {
            Flash::error('Notification not found');

            return redirect(route('notifications.index'));
        }

        $this->notificationRepository->delete($id);

        Flash::success('Notification deleted successfully.');

        return redirect(route('notifications.index'));
    }
}