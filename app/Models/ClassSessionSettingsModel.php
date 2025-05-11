<?php

namespace App\Models;

use CodeIgniter\Model;

class ClassSessionSettingsModel extends Model
{
    protected $table = 'class_session_settings';
    protected $primaryKey = 'class_session_settings_id';
    protected $allowedFields = [
        'attendance_method', 'time_in_threshold', 'time_out_threshold', 'late_threshold',
        'auto_create_session', 'auto_mark_attendance', 'deleted_at'
    ];
    protected $useSoftDeletes = true;
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'attendance_method' => 'required|in_list[manual,automatic]',
        'time_in_threshold' => 'required|valid_time',
        'time_out_threshold' => 'required|valid_time',
        'late_threshold' => 'required|valid_time',
        'auto_create_session' => 'required|in_list[yes,no]',
        'auto_mark_attendance' => 'required|in_list[yes,no]'
    ];

    protected $validationMessages = [
        'attendance_method' => [
            'required' => 'The attendance method is required.',
            'in_list' => 'The attendance method must be one of: manual, automatic.'
        ],
        'time_in_threshold' => [
            'required' => 'The time in threshold is required.',
            'valid_time' => 'The time in threshold must be a valid time.'
        ],
        'time_out_threshold' => [
            'required' => 'The time out threshold is required.',
            'valid_time' => 'The time out threshold must be a valid time.'
        ],
        'late_threshold' => [
            'required' => 'The late threshold is required.',
            'valid_time' => 'The late threshold must be a valid time.'
        ],
        'auto_create_session' => [
            'required' => 'The auto create session setting is required.',
            'in_list' => 'The auto create session setting must be one of: yes, no.'
        ],
        'auto_mark_attendance' => [
            'required' => 'The auto mark attendance setting is required.',
            'in_list' => 'The auto mark attendance setting must be one of: yes, no.'
        ]
    ];

    public function getSettings($settingsId, $withDeleted = false)
    {
        if ($withDeleted) {
            return $this->withDeleted()->find($settingsId);
        }
        return $this->find($settingsId);
    }

    public function updateSettings($settingsId, $settingsData)
    {
        $settingsData['class_session_settings_id'] = $settingsId;
        if (!$this->validate($settingsData)) {
            throw new \Exception(implode(', ', $this->errors()));
        }
        unset($settingsData['class_session_settings_id']);
        return $this->update($settingsId, $settingsData);
    }

    public function softDelete($settingsId)
    {
        try {
            $this->delete($settingsId);
            return true;
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }

    public function restore($settingsId)
    {
        try {
            $this->onlyDeleted()->update($settingsId, ['deleted_at' => null]);
            return true;
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }
}