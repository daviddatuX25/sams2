<?php
namespace App\Services;

use App\Models\UserModel;
use App\Models\NotificationModel;
use CodeIgniter\Validation\Exceptions\ValidationException;
use CodeIgniter\HTTP\Files\UploadedFile;

class UserService
{
    protected $userRole;
    protected ?UserModel $userModel;
    protected ?NotificationModel $notificationModel;

    public function __construct(
        ?string $userRole = null,
        ?UserModel $userModel = null,
        ?NotificationModel $notificationModel = null
    ) {
        $this->userRole = $userRole ?? session()->get('role');
        $this->userModel = $userModel ?? new UserModel();
        $this->notificationModel = $notificationModel;
    }

    public function login(string $userKey, string $password, ?string $role = null): array
    {
        $user = $this->userModel->where('user_key', $userKey)->first();

        if (!$user) {
            throw new \Exception('Invalid credentials');
        }

        $isPasswordValid = false;

        if ($user['is_password_temporary']) {
            $isPasswordValid = $password === $user['password_hash'];
        } else {
            $isPasswordValid = password_verify($password, $user['password_hash']);
        }

        if (!$isPasswordValid) {
            throw new \Exception('Invalid credentials');
        }

        if ($role && $user['role'] !== $role) {
            throw new \Exception('Invalid role');
        }

        if ($user['status'] !== 'active') {
            throw new \Exception('Account is not active');
        }

        if ($user) {
            session()->set([
                'user_id' => $user['user_id'],
                'role' => $user['role'],
                'first_name' => $user['first_name'],
                'profile_picture' => $user['profile_picture'],
                'isLoggedIn' => true,
                'is_password_temporary' => $user['is_password_temporary'],
                'activeTerm' => (new \App\Models\EnrollmentTermModel)->getActiveTerm()
            ]);
        }

        return $user;
    }

    public function register(array $userData): array
    {
        $userData['password_hash'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        $userData['is_password_temporary'] = 0;
        $userData['status'] = 'pending';
        unset($userData['password']);
        $userId = $this->userModel->insert($userData);
        if (!$userId) {
            throw new \Exception('Registration failed');
        }
        return $this->userModel->find($userId);
    }

    public function getUser(int $userId): ?array
    {
        if (!in_array($this->userRole, ['student', 'teacher', 'admin'])) {
            throw new ValidationException('Role must be one of: student, teacher, admin.');
        }

        if ($this->userRole !== 'admin' && $userId !== (int)session()->get('user_id')) {
            throw new ValidationException('You can only view your own profile.');
        }

        $user = $this->userModel
            ->select('user_id, user_key, first_name, last_name, middle_name, birthday, gender, bio, profile_picture')
            ->where('user_id', $userId)
            ->where('deleted_at IS NULL')
            ->first();

        return $user ?: null;
    }

    public function updateProfile(int $userId, array $userData): bool
    {
        if (!in_array($this->userRole, ['student', 'teacher', 'admin'])) {
            throw new ValidationException('Role must be one of: student, teacher, admin.');
        }

        if ($this->userRole !== 'admin' && $userId !== (int)session()->get('user_id')) {
            throw new ValidationException('You can only update your own profile.');
        }

        $currentUser = $this->userModel
            ->select('user_id, user_key, first_name, last_name, middle_name, birthday, gender, bio')
            ->where('user_id', $userId)
            ->where('deleted_at IS NULL')
            ->first();
        if (!$currentUser) {
            throw new ValidationException('User not found.');
        }

        $allowedFields = ['user_key', 'first_name', 'last_name', 'middle_name', 'birthday', 'gender', 'bio'];
        $data = array_intersect_key($userData, array_flip($allowedFields));

        // Check if data has changes
        $unchanged = true;
        foreach ($data as $key => $value) {
            if ((string)($currentUser[$key] ?? '') !== (string)($value ?? '')) {
                $unchanged = false;
                break;
            }
        }
        if ($unchanged) {
            throw new ValidationException('No changes detected in submitted profile data.');
        }

        // Check for existing user_key (must not belong to another user)
        if (isset($data['user_key'])) {
            $existingUser = $this->userModel
                ->where('user_key', $data['user_key'])
                ->first();
            if ($existingUser) {
                if ($currentUser['user_key'] != $existingUser['user_key']) {
                    throw new ValidationException('The user key is already taken.');
                } else {
                    unset($data['user_key']);
                }
            }
        }

        return $this->userModel->update($userId, $data);
    }

    public function updateProfilePicture(int $userId, string $photoPath): bool
    {
        if (!in_array($this->userRole, ['student', 'teacher', 'admin'])) {
            throw new ValidationException('Role must be one of: student, teacher, admin.');
        }

        if ($this->userRole !== 'admin' && $userId !== (int)session()->get('user_id')) {
            throw new ValidationException('You can only update your own profile picture.');
        }

        return $this->userModel->update($userId, ['profile_picture' => $photoPath]);
    }

    public function changePassword(int $userId, string $oldPassword, string $newPassword): bool
    {
        if (!in_array($this->userRole, ['student', 'teacher', 'admin'])) {
            throw new ValidationException('Role must be one of: student, teacher, admin.');
        }

        if ($this->userRole !== 'admin' && $userId !== (int)session()->get('user_id')) {
            throw new ValidationException('You can only change your own password.');
        }

        $user = $this->userModel
            ->where('user_id', $userId)
            ->where('deleted_at IS NULL')
            ->first();

        if (!$user) {
            throw new ValidationException('User not found.');
        }

        $isTemporary = isset($user['is_password_temporary']) && (int)$user['is_password_temporary'] === 1;

        if (!$isTemporary && !password_verify($oldPassword, $user['password_hash'])) {
            throw new ValidationException('Incorrect old password.');
        }

        if ($isTemporary && $oldPassword !== $user['password_hash']) {
            throw new ValidationException('Incorrect temporary password.');
        }

        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

        return $this->userModel->update($userId, [
            'password_hash' => $newPasswordHash,
            'is_password_temporary' => 0
        ]);
    }

    public function handleProfileAction(int $userId, string $action, array $postData, ?UploadedFile $file = null, bool $isAjax = false): array
    {
        if (!in_array($this->userRole, ['student', 'teacher', 'admin'])) {
            $response = [
                'success' => false,
                'message' => 'Role must be one of: student, teacher, admin.',
                'errors' => ['role' => 'Invalid role'],
                'error_code' => 'invalid_role'
            ];
            if ($isAjax) {
                return $response;
            }
            throw new ValidationException($response['message']);
        }

        if ($this->userRole !== 'admin' && $userId !== (int)session()->get('user_id')) {
            $response = [
                'success' => false,
                'message' => 'You can only modify your own profile.',
                'errors' => ['user_id' => 'Unauthorized access'],
                'error_code' => 'unauthorized_access'
            ];
            if ($isAjax) {
                return $response;
            }
            throw new ValidationException($response['message']);
        }

        $user = $this->userModel->where('user_id', $userId)->where('deleted_at IS NULL')->first();
        if (!$user) {
            $response = [
                'success' => false,
                'message' => 'User not found.',
                'errors' => ['user_id' => 'User ID does not exist'],
                'error_code' => 'user_id_not_found'
            ];
            if ($isAjax) {
                return $response;
            }
            throw new ValidationException($response['message']);
        }

        $validation = \Config\Services::validation();
        $response = ['success' => false, 'message' => '', 'errors' => [], 'error_code' => null];

        if ($action === 'update_profile') {
            $rules = [
                'user_key' => 'required|min_length[3]|max_length[50]',
                'first_name' => 'required|min_length[2]|max_length[50]',
                'last_name' => 'required|min_length[2]|max_length[50]',
                'middle_name' => 'permit_empty|max_length[50]',
                'birthday' => 'required|valid_date',
                'gender' => 'required|in_list[male,female,other]',
                'bio' => 'permit_empty|max_length[500]'
            ];

            if (!$validation->setRules($rules)->run($postData)) {
                $response['errors'] = $validation->getErrors();
                $response['message'] = 'Profile update failed.';
                $response['error_code'] = 'validation_failed';
                if ($isAjax) {
                    return $response;
                }
                throw new ValidationException(implode(', ', $response['errors']));
            }

            $userData = array_intersect_key($postData, array_flip(['user_key', 'first_name', 'last_name', 'middle_name', 'birthday', 'gender', 'bio']));
            try {
                if ($this->updateProfile($userId, $userData)) {
                    session()->set('first_name', $userData['first_name']);
                    $response['success'] = true;
                    $response['message'] = 'Profile updated successfully.';
                    $response['user'] = $this->userModel->find($userId);
                } else {
                    $response['message'] = 'Failed to update profile.';
                    $response['error_code'] = 'database_error';
                }
            } catch (ValidationException $e) {
                $response['message'] = $e->getMessage();
                $response['errors'] = ['general' => $e->getMessage()];
                if (strpos($e->getMessage(), 'user key') !== false) {
                    $response['errors'] = ['user_key' => $e->getMessage()];
                    $response['error_code'] = 'user_key_exists';
                } elseif (strpos($e->getMessage(), 'No changes') !== false) {
                    $response['error_code'] = 'no_changes_detected';
                } elseif (strpos($e->getMessage(), 'User not found') !== false) {
                    $response['error_code'] = 'user_id_not_found';
                }
                if ($isAjax) {
                    return $response;
                }
                throw $e;
            }
        } elseif ($action === 'update_photo') {
            if (!$file || !$file->isValid() || !in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png'])) {
                $response['message'] = 'Invalid file. Only JPG, JPEG, PNG allowed.';
                $response['errors'] = ['profile_picture' => 'Invalid file type'];
                $response['error_code'] = 'invalid_file_type';
                if ($isAjax) {
                    return $response;
                }
                throw new ValidationException($response['message']);
            }

            $newName = 'user_' . $userId . '_' . time() . '.' . $file->getExtension();
            try {
                $file->move(ROOTPATH . 'public/uploads/profile_pictures', $newName);
            } catch (\Exception $e) {
                $response['message'] = 'Failed to upload file.';
                $response['errors'] = ['profile_picture' => 'File upload failed'];
                $response['error_code'] = 'file_upload_failed';
                if ($isAjax) {
                    return $response;
                }
                throw new ValidationException($response['message']);
            }
            $photoPath = '/uploads/profile_pictures/' . $newName;

            if ($this->updateProfilePicture($userId, $photoPath)) {
                session()->set('profile_picture', $photoPath);
                $response['success'] = true;
                $response['message'] = 'Profile picture updated successfully.';
                $response['data'] = ['profile_picture' => $photoPath];
            } else {
                $response['message'] = 'Failed to update profile picture.';
                $response['error_code'] = 'database_error';
                if ($isAjax) {
                    return $response;
                }
                throw new ValidationException($response['message']);
            }
        } elseif ($action === 'change_password') {
            $rules = [
                'old_password' => 'required',
                'new_password' => 'required|min_length[8]|max_length[255]',
                'confirm_password' => 'required|matches[new_password]'
            ];

            if (!$validation->setRules($rules)->run($postData)) {
                $response['errors'] = $validation->getErrors();
                $response['message'] = 'Password change failed.';
                $response['error_code'] = 'validation_failed';
                if ($isAjax) {
                    return $response;
                }
                throw new ValidationException(implode(', ', $response['errors']));
            }

            try {
                if ($this->changePassword($userId, $postData['old_password'], $postData['new_password'])) {
                    $response['success'] = true;
                    $response['message'] = 'Password changed successfully.';
                } else {
                    $response['message'] = 'Failed to change password.';
                    $response['error_code'] = 'database_error';
                }
            } catch (ValidationException $e) {
                $response['message'] = $e->getMessage();
                $response['errors'] = ['general' => $e->getMessage()];
                if (strpos($e->getMessage(), 'old password') !== false) {
                    $response['errors'] = ['old_password' => $e->getMessage()];
                    $response['error_code'] = strpos($e->getMessage(), 'temporary') !== false ? 'incorrect_temporary_password' : 'incorrect_old_password';
                } elseif (strpos($e->getMessage(), 'User not found') !== false) {
                    $response['error_code'] = 'user_id_not_found';
                }
                if ($isAjax) {
                    return $response;
                }
                throw $e;
            }
        } else {
            $response['message'] = 'Invalid action.';
            $response['errors'] = ['action' => 'Unknown action'];
            $response['error_code'] = 'invalid_action';
            if ($isAjax) {
                return $response;
            }
            throw new ValidationException($response['message']);
        }

        return $response;
    }

    public function logout(): bool
    {
        session()->destroy();
        return true;
    }

    public function getUnreadNotificationCount(int $userId): int
    {
        if (!$this->notificationModel) {
            return 0;
        }
        return $this->notificationModel
            ->where('user_id', $userId)
            ->where('is_read', 0)
            ->where('deleted_at IS NULL')
            ->countAllResults();
    }
}