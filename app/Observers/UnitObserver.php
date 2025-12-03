<?php

namespace App\Observers;

use App\Models\Unit;
use App\Models\Project;

class UnitObserver
{
    /**
     * Handle the Unit "saved" event.
     */
    public function saved(Unit $unit)
    {
        $this->updateProjectStatusAndDelivery($unit->project_id);
    }

    /**
     * Handle the Unit "updated" event.
     */
    public function updated(Unit $unit)
    {
        // If the unit was moved to a different project
        if ($unit->wasChanged('project_id')) {
             $originalProjectId = $unit->getOriginal('project_id');
             if ($originalProjectId) {
                 $this->updateProjectStatusAndDelivery($originalProjectId);
             }
        }
        $this->updateProjectStatusAndDelivery($unit->project_id);
    }

    /**
     * Handle the Unit "deleted" event.
     */
    public function deleted(Unit $unit)
    {
        $this->updateProjectStatusAndDelivery($unit->project_id);
    }

    protected function updateProjectStatusAndDelivery($projectId)
    {
        if (!$projectId) {
            return;
        }

        $project = Project::find($projectId);
        if (!$project) {
            return;
        }

        $units = $project->units;

        // 1. Calculate Delivery Year (Earliest)
        $minDelivery = $units->min('delivery_year');

        // 2. Calculate Status (Highest Hierarchy)
        // Hierarchy: livable > ready_to_move > under_construction > off_plan > new_launch
        $hierarchy = [
            'livable' => 5,
            'ready_to_move' => 4,
            'under_construction' => 3,
            'off_plan' => 2,
            'new_launch' => 1,
        ];

        $highestStatus = 'new_launch';
        $highestScore = 0;

        foreach ($units as $unit) {
            // Check construction_status (if exists) or fallback logic?
            // We assume 'construction_status' is the field now.
            $status = $unit->construction_status;

            // Normalize status (handle null or unexpected)
            if (!$status) continue;

            $score = $hierarchy[$status] ?? 0;

            if ($score > $highestScore) {
                $highestScore = $score;
                $highestStatus = $status;
            }
        }

        // If no units, maybe keep existing or default?
        // User said: "Project status is FROM the units". If no units, we can't really say.
        // But let's only update if we found something.

        $updateData = [];

        if ($minDelivery) {
            $updateData['delivery_year'] = $minDelivery;
        }

        if ($highestScore > 0) {
            $updateData['status'] = $highestStatus;
        }

        if (!empty($updateData)) {
            $project->update($updateData);
        }
    }
}
