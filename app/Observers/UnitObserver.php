<?php

namespace App\Observers;

use App\Models\Unit;

class UnitObserver
{
    /**
     * Handle the Unit "saved" event.
     */
    public function saved(Unit $unit): void
    {
        if ($unit->project_id) {
            $project = $unit->project;
            if ($project) {
                $project->updateStatusFromUnits();
                $project->updateDeliveryYearFromUnits();
            }
        }
    }

    /**
     * Handle the Unit "deleted" event.
     */
    public function deleted(Unit $unit): void
    {
        if ($unit->project_id) {
            $project = $unit->project;
            if ($project) {
                $project->updateStatusFromUnits();
                $project->updateDeliveryYearFromUnits();
            }
        }
    }
}
