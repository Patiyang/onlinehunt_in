<?php

namespace App\Controllers;
use App\Services\FirebaseNotificationService;

// use App\Controllers\BaseController;

class NotificationController extends BaseAdminController
{
     protected $firebaseNotificationService;

    public function __construct()
    {
 $this->firebaseNotificationService =
            new FirebaseNotificationService();    }

    /**
     * Display notification form
     */
    public function index()
    {
        checkPermission('send_notifications');

        return view('admin/notification/form', [
            'title'  => trans('send_notification'),
            'action' => adminUrl('notifications/send')
        ]);
    }

    /**
     * Send notification
     */
    public function send()
    {
        checkPermission('send_notifications');

        if (!isPostMethod()) {
            return redirect()->to(adminUrl('notifications'));
        }

        $postData = $this->request->getPost();

        $title = trim($postData['title'] ?? '');
        $message = trim($postData['message'] ?? '');
        $imageId = (int)($postData['image_id'] ?? 0);

        /*
         * Validate title
         */
        if (empty($title)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', trans('title') . ' is required');
        }

        /*
         * Validate message
         */
        if (empty($message)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', trans('message') . ' is required');
        }

        /*
         * Resolve selected image
         */
        $imageUrl = '';

        if ($imageId > 0) {

            $image = model('ImageModel')->find($imageId);

            if ($image) {

                $imageUrl = getStorageFileUrl(
                    $image->image_mid,
                    $image->storage
                );
            }
        }

        /*
         * Send Firebase notification
         */
        try {

            $this->firebaseNotificationService->sendToTopic(
                'all',
                $title,
                $message,
                [
                    'type'      => 'notification',
                    'id'        => '0',
                    'live_url'  => '',
                    'slug'      => '',
                    'image_url' => $imageUrl
                ]
            );

            setSuccessMessage(
                trans('notification_sent_successfully'),
                false
            );

            return redirect()->to(
                adminUrl('notifications')
            );

        } catch (\Throwable $e) {

            log_message(
                'error',
                'Firebase notification failed: ' . $e->getMessage()
            );

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    trans('notification_send_failed')
                );
        }
    }
}