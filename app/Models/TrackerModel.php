<?php

namespace App\Models;

use CodeIgniter\Model;

class TrackerModel extends Model
{
    protected $table = 'trackers';
    protected $primaryKey = 'tracker_id';
    protected $allowedFields = ['tracker_name', 'tracker_description', 'tracker_type', 'status', 'deleted_at'];
    protected $useSoftDeletes = true;
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'tracker_name' => 'required|is_unique[trackers.tracker_name,tracker_id,{tracker_id}]|max_length[255]',
        'tracker_description' => 'permit_empty',
        'tracker_type' => 'required|in_list[face,rfid]',
        'status' => 'required|in_list[active,inactive]'
    ];

    protected $validationMessages = [
        'tracker_name' => [
            'required' => 'The tracker name is required.',
            'is_unique' => 'The tracker name must be unique.',
            'max_length' => 'The tracker name cannot exceed 255 characters.'
        ],
        'tracker_type' => [
            'required' => 'The tracker type is required.',
            'in_list' => 'The tracker type must be one of: face, rfid.'
        ],
        'status' => [
            'required' => 'The status is required.',
            'in_list' => 'The status must be one of: active, inactive.'
        ]
    ];

    public function getTracker($trackerId, $withDeleted = false)
    {
        if ($withDeleted) {
            return $this->withDeleted()->find($trackerId);
        }
        return $this->find($trackerId);
    }

    public function createTracker($trackerData)
    {
        if (!$this->validate($trackerData)) {
            throw new \Exception(implode(', ', $this->errors()));
        }
        $this->insert($trackerData);
        $trackerId = $this->getInsertID();
        return $this->find($trackerId);
    }

    public function updateTracker($trackerId, $trackerData)
    {
        $trackerData['tracker_id'] = $trackerId;
        if (!$this->validate($trackerData)) {
            throw new \Exception(implode(', ', $this->errors()));
        }
        unset($trackerData['tracker_id']);
        return $this->update($trackerId, $trackerData);
    }

    public function getActiveTrackers($withDeleted = false)
    {
        if ($withDeleted) {
            return $this->withDeleted()->where('status', 'active')->findAll();
        }
        return $this->where('status', 'active')->findAll();
    }

    public function getTrackersByType($type, $withDeleted = false) 
    {
        $builder = $this->where('tracker_type', $type);
        if ($withDeleted) $builder->withDeleted();
        return $builder->findAll();
    }

    public function softDelete($trackerId)
    {
        try {
            $this->delete($trackerId);
            return true;
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }

    public function restore($trackerId)
    {
        try {
            $this->onlyDeleted()->update($trackerId, ['deleted_at' => null]);
            return true;
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }
}