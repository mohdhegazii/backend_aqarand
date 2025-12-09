<?php

namespace App\View\Components\Lookup;

use App\Services\LookupService;
use Illuminate\View\Component;
use Illuminate\Support\Collection;

class PropertyHierarchyPicker extends Component
{
    public $categories;
    public $propertyTypes; // For preloading
    public $unitTypes;     // For preloading

    public $selectedCategoryId;
    public $selectedPropertyTypeId;
    public $selectedUnitTypeId;

    public $categoryFieldName;
    public $propertyTypeFieldName;
    public $unitTypeFieldName;

    public $required;

    protected $lookupService;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        LookupService $lookupService,
        $selectedCategoryId = null,
        $selectedPropertyTypeId = null,
        $selectedUnitTypeId = null,
        $categoryFieldName = 'category_id',
        $propertyTypeFieldName = 'property_type_id',
        $unitTypeFieldName = 'unit_type_id',
        $required = true
    ) {
        $this->lookupService = $lookupService;

        $this->selectedCategoryId = $selectedCategoryId;
        $this->selectedPropertyTypeId = $selectedPropertyTypeId;
        $this->selectedUnitTypeId = $selectedUnitTypeId;

        $this->categoryFieldName = $categoryFieldName;
        $this->propertyTypeFieldName = $propertyTypeFieldName;
        $this->unitTypeFieldName = $unitTypeFieldName;

        $this->required = filter_var($required, FILTER_VALIDATE_BOOLEAN);

        $this->categories = $this->lookupService->getActiveCategories();
        $this->propertyTypes = collect([]);
        $this->unitTypes = collect([]);

        // Preload Property Types if Category is selected
        if ($this->selectedCategoryId) {
            $this->propertyTypes = $this->lookupService->getActivePropertyTypesByCategory($this->selectedCategoryId);
        }

        // Preload Unit Types if Property Type is selected
        if ($this->selectedPropertyTypeId) {
            $this->unitTypes = $this->lookupService->getActiveUnitTypesByPropertyType($this->selectedPropertyTypeId);
        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.lookup.property-hierarchy-picker');
    }
}
