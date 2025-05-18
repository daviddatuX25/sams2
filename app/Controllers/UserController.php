<?php

namespace App\Controllers;

use App\Services\UserService;
use App\Services\NotificationService;
use App\Traits\ExceptionHandlingTrait;

class UserController extends BaseController
{
    use ExceptionHandlingTrait;

    public function profile()
    {
        return $this->handleAction(function () {
            $userId = session()->get('user_id');
            $role = session()->get('role'); // Ensure you store role in session during login
            $userService = new UserService;
            if ($this->request->getMethod() === 'POST') {
                $action = $this->request->getPost('action');
                $postData = $this->request->getPost();
                $file = $this->request->getFile('profile_picture');

                if ($file && !$file->isValid()) {
                    $file = null;
                }

                $result = $userService->handleProfileAction($userId, $action, $postData, $file, $this->request->isAJAX());

                if ($this->request->isAJAX()) {
                    return $this->response->setJSON($result);
                }

                if ($result['success']) {
                    session()->setFlashdata('success', $result['message']);
                } else {
                    session()->setFlashdata('error', $result['message']);
                }

                return redirect()->to("/$role/profile");
            }

            $user = $userService->getUser($userId);
            if (!$user) {
                throw new NotFoundException('User not found.');
            }

            return view('shared/profile', [
                'user' => $user,
                'urlRedirect' => 'user/profile', 
                'navbar' => $role,
                'currentSegment' => 'profile',
                'validation' => \Config\Services::validation()
            ]);
        });
    }

    public function notification()
    {
        return $this->handleAction(function () {
            log_message('debug', 'SharedController::notification called with method: ' . $this->request->getMethod() . 
                ', session: ' . json_encode(session()->get()) . 
                ', X-Requested-With: ' . $this->request->getHeaderLine('X-Requested-With') . 
                ', Content-Type: ' . $this->request->getHeaderLine('Content-Type'));

            if (!$this->request->isAJAX() && !$this->isApiRequest()) {
                log_message('error', 'Non-AJAX request to notification endpoint');
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'This endpoint requires an AJAX or API request.'
                ])->setStatusCode(400);
            }

            $notificationService = new NotificationService;
            $session = session();
            $userId = $session->get('user_id');
            $role = $session->get('role');

            if (!is_numeric($userId) || !in_array($role, ['student', 'teacher', 'admin'])) {
                log_message('error', 'Notification: Unauthorized access. user_id: ' . var_export($userId, true) . ', role: ' . var_export($role, true));
                throw new \App\Exceptions\UnauthorizedException('Please log in to access notifications.', 401);
            }

            $userId = (int)$userId;

            if ($this->request->getMethod() === 'POST') {
                // Log raw input if JSON parsing fails
                $rawInput = $this->request->getRawInput();
                log_message('debug', 'Raw POST input: ' . json_encode($rawInput));

                // Parse JSON body
                $jsonData = $this->request->getJSON(true); // true returns array
                if ($jsonData === null) {
                    $rawBody = file_get_contents('php://input');
                    log_message('error', 'Failed to parse JSON body. Raw body: ' . $rawBody);
                    throw new \App\Exceptions\ValidationException('Invalid JSON body.', 400);
                }

                log_message('debug', 'Parsed POST body: ' . json_encode($jsonData));

                $action = $jsonData['action'] ?? null;
                $notificationId = isset($jsonData['notification_id']) ? (int)$jsonData['notification_id'] : 0;

                if (!in_array($action, ['markAsRead', 'delete'])) {
                    log_message('error', 'Invalid or missing action: ' . var_export($action, true));
                    throw new \App\Exceptions\ValidationException('Invalid action. Must be markAsRead or delete.', 400);
                }

                if ($notificationId <= 0) {
                    log_message('error', 'Invalid notification ID: ' . $notificationId);
                    throw new \App\Exceptions\ValidationException('Invalid notification ID.', 400);
                }                
                if ($action === 'markAsRead') {
                    $notificationService->markNotificationRead($userId, $notificationId);
                    $message = 'Notification marked as read.';

                } else {
                    $notificationService->deleteNotification($userId, $notificationId);
                    $message = 'Notification deleted.';
                }

                return $this->response->setJSON([
                    'success' => true,
                    'message' => $message
                ])->setStatusCode(200);
            }

            // GET request: Fetch notifications with pagination
            $status = $this->request->getGet('status');
            $page = (int)$this->request->getGet('page', FILTER_VALIDATE_INT) ?: 1;
            $perPage = (int)$this->request->getGet('perPage', FILTER_VALIDATE_INT) ?: 10;

            $notifications = $notificationService->getNotifications($userId, $status, $page, $perPage);
            $totalNotifications = $notificationService->countNotifications($userId, $status);
            $notificationCount = $notificationService->countUnreadNotifications($userId);

            return $this->response->setJSON([
                'success' => true,
                'data' => $notifications,
                'pagination' => [
                    'page' => $page,
                    'perPage' => $perPage,
                    'total' => $totalNotifications,
                    'hasMore' => ($page * $perPage) < $totalNotifications
                ],
                'notificationCount' => $notificationCount
            ])->setStatusCode(200);
        });
    }

    public function logout()
    {
        if ((new UserService())->logout()) {
            return redirect()->to('auth/student/login');
        }
        throw new \Exception('Logout failed.');
    }
}
