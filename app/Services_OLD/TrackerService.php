<?php

namespace App\Services;

use App\Models\TrackerModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class TrackerService
{
    protected $trackerModel;

    public function __construct(TrackerModel $trackerModel)
    {
        $this->trackerModel = $trackerModel;
    }

    public function getTracker(int $trackerId): array
    {
        $tracker = $this->trackerModel->find($trackerId);
        if (!$tracker) {
            throw new PageNotFoundException('Tracker not found');
        }
        return $tracker;
    }

    public function getActiveTrackers(): array
    {
        return $this->trackerModel
            ->where('status', 'active')
            ->where('deleted_at IS NULL')
            ->findAll();
    }
}