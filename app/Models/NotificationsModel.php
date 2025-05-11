<?php
namespace App\Models;

use CodeIgniter\Model;

class NotificationsModel extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'notif_id';
    protected $allowedFields = ['user_id', 'message', 'create_datetime', 'read_datetime', 'deleted_at'];
    protected $useSoftDeletes = true;
    protected $deletedField = 'deleted_at';

    // Automatically set create_datetime on insert
    protected $beforeInsert = ['setCreateDatetime'];

    protected $validationRules = [
        'user_id' => 'required|is_not_unique[users.user_id]',
        'message' => 'required',
        'create_datetime' => 'permit_empty|valid_date',
        'read_datetime' => 'permit_empty|valid_date'
    ];

    protected $validationMessages = [
        'user_id' => [
            'required' => 'The user ID is required.',
            'is_not_unique' => 'The user ID must reference an existing user.'
        ],
        'message' => [
            'required' => 'The message is required.'
        ],
        'create_datetime' => [
            'valid_date' => 'The creation datetime must be a valid date.'
        ],
        'read_datetime' => [
            'valid_date' => 'The read datetime must be a valid date.'
        ]
    ];

    // Callback to set create_datetime before insert
    protected function setCreateDatetime(array $data)
    {
        if (!isset($data['data']['create_datetime'])) {
            $data['data']['create_datetime'] = date('Y-m-d H:i:s');
        }
        return $data;
    }

    public function getNotification($notifId, $withDeleted = false)
    {
        if ($withDeleted) {
            return $this->withDeleted()->find($notifId);
        }
        return $this->find($notifId);
    }

    public function notificationExists($notifId, $withDeleted = false)
    {
        $builder = $this->builder();
        if ($withDeleted) {
            $builder->withDeleted();
        }
        return $builder->where('notif_id', $notifId)->get()->getRow() !== null;
    }

    public function createNotification($notifData)
    {
        if (!$this->validate($notifData)) {
            throw new \Exception(implode(', ', $this->errors()));
        }
        $this->insert($notifData);
        $notifId = $this->getInsertID();
        return $this->select('notif_id, user_id, message, create_datetime, read_datetime')->find($notifId);
    }

    public function updateNotification($notifId, $notifData)
    {
        $notifData['notif_id'] = $notifId;
        if (!$this->validate($notifData)) {
            throw new \Exception(implode(', ', $this->errors()));
        }
        unset($notifData['notif_id']);
        return $this->update($notifId, $notifData);
    }

    // Get unread notifications (read_datetime is NULL)
    public function getUnreadNotifications($userId, $withDeleted = false)
    {
        if ($withDeleted) {
            return $this->withDeleted()->where('user_id', $userId)->where('read_datetime IS NULL')->findAll();
        }
        return $this->where('user_id', $userId)->where('read_datetime IS NULL')->findAll();
    }

    // Mark notification as read by setting read_datetime
    public function markAsRead($notifId)
    {
        return $this->update($notifId, ['read_datetime' => date('Y-m-d H:i:s')]);
    }

    public function searchNotifications($searchTerm, $withDeleted = false)
    {
        $builder = $this->builder();
        if ($withDeleted) {
            $builder->withDeleted();
        }
        return $builder->like('message', $searchTerm)
                       ->orLike('create_datetime', $searchTerm)
                       ->get()->getResultArray();
    }

    public function softDelete($notifId)
    {
        try {
            $this->delete($notifId);
            return true;
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }

    public function hardDelete($notifId)
    {
        try {
            $this->onlyDeleted()->delete($notifId, true);
            return true;
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }

    public function restore($notifId)
    {
        try {
            $this->onlyDeleted()->update($notifId, ['deleted_at' => null]);
            return true;
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }

    /**
     * Bulk create notifications for multiple users.
     *
     * @param array $notifications Array of ['user_id' => int, 'message' => string]
     * @return array ['success' => array, 'failed' => array]
     */
    public function bulkCreateNotifications(array $notifications)
    {
        $success = [];
        $failed = [];
        $userModel = new \App\Models\UserModel();

        foreach ($notifications as $index => $notifData) {
            try {
                // Validate user exists
                if (!$userModel->userExists($notifData['user_id'])) {
                    $failed[] = "Notification $index: User ID {$notifData['user_id']} does not exist.";
                    continue;
                }

                // Prepare data
                $data = [
                    'user_id' => $notifData['user_id'],
                    'message' => $notifData['message'],
                    'create_datetime' => date('Y-m-d H:i:s'), // Set explicitly for batch insert
                    'read_datetime' => null
                ];

                if (!$this->validate($data)) {
                    $failed[] = "Notification $index: " . implode(', ', $this->errors());
                    continue;
                }

                $this->insert($data);
                $notifId = $this->getInsertID();
                $success[] = "Notification $index: Created for user {$notifData['user_id']} (ID: $notifId).";
            } catch (\Exception $e) {
                $failed[] = "Notification $index: " . $e->getMessage();
            }
        }

        return ['success' => $success, 'failed' => $failed];
    }

    /**
     * Bulk soft delete notifications by IDs.
     *
     * @param array $notifIds Array of notification IDs
     * @return array ['success' => array, 'failed' => array]
     */
    public function bulkDeleteNotifications(array $notifIds)
    {
        $success = [];
        $failed = [];

        foreach ($notifIds as $notifId) {
            try {
                if (!$this->notificationExists($notifId)) {
                    $failed[] = "Notification ID $notifId: Not found.";
                    continue;
                }

                if ($this->softDelete($notifId)) {
                    $success[] = "Notification ID $notifId: Deleted successfully.";
                } else {
                    $failed[] = "Notification ID $notifId: Failed to delete.";
                }
            } catch (\Exception $e) {
                $failed[] = "Notification ID $notifId: " . $e->getMessage();
            }
        }

        return ['success' => $success, 'failed' => $failed];
    }
}