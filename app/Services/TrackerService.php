<?php

namespace App\Services;

use App\Models\TrackerModel;
use App\Traits\ServiceExceptionTrait;

class TrackerService
{
    use ServiceExceptionTrait;

    protected ?TrackerModel $trackerModel;

    public function __construct(?TrackerModel $trackerModel = null)
    {
        $this->trackerModel = $trackerModel ?? new TrackerModel();
    }

    /**
     * Create a new tracker.
     */
    public function createTracker(array $data): int
    {

        $this->trackerModel->insert($data);
        return $this->trackerModel->insertID();
    }

    /**
     * Retrieve trackers.
     */
    public function getTrackers(): array
    {
        return $this->trackerModel
            ->select('tracker_id, tracker_name, tracker_description, tracker_type, status')
            ->where('deleted_at IS NULL')
            ->findAll();
    }

    /**
     * Update a tracker.
     */
    public function updateTracker(int $trackerId, array $data): bool
    {
        $tracker = $this->trackerModel->where('tracker_id', $trackerId)->where('deleted_at IS NULL')->first();
        if (!$tracker) {
            $this->throwNotFound('Tracker', $trackerId);
        }
        return $this->trackerModel->update($trackerId, $data);
    }

    /**
     * Soft delete a tracker.
     */
    public function deleteTracker(int $trackerId): bool
    {
        $tracker = $this->trackerModel->where('tracker_id', $trackerId)->where('deleted_at IS NULL')->first();
        if (!$tracker) {
            $this->throwNotFound('Tracker', $trackerId);
        }

        return $this->trackerModel->delete($trackerId);
    }
}