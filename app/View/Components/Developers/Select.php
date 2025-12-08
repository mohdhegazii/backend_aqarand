<?php

namespace App\View\Components\Developers;

use App\Models\Developer;
use App\Services\DeveloperService;
use Illuminate\View\Component;

class Select extends Component
{
    public $name;
    public $selectedId;
    public $selectedDeveloper;
    public $placeholder;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        $name = 'developer_id',
        $selectedId = null,
        $placeholder = null
    ) {
        $this->name = $name;
        $this->selectedId = $selectedId;
        $this->placeholder = $placeholder;

        if ($this->selectedId) {
            // Attempt to load using DeveloperService for consistent logic
            $service = app(DeveloperService::class);
            $this->selectedDeveloper = $service->findActiveDeveloperById($this->selectedId);

            // Fallback: if not active or not found by service, try direct model
            // This ensures we can still see the selected value even if it's inactive (e.g. legacy data)
            if (!$this->selectedDeveloper && $this->selectedId) {
                 $this->selectedDeveloper = Developer::find($this->selectedId);
            }
        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.developers.select');
    }
}
