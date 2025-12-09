@props(['currentStep' => 1, 'projectId' => null])

@php
    $steps = [
        1 => [
            'label' => __('admin.project_wizard.step_basics'),
            'route' => 'admin.projects.steps.basics',
        ],
        2 => [
            'label' => __('admin.amenities'),
            'route' => 'admin.projects.steps.amenities',
        ],
        3 => [
            'label' => __('admin.marketing') !== 'admin.marketing' ? __('admin.marketing') : 'Marketing',
            'route' => 'admin.projects.steps.marketing',
        ],
        4 => [
            'label' => __('admin.media') !== 'admin.media' ? __('admin.media') : 'Media',
            'route' => 'admin.projects.steps.media',
        ],
        5 => [
            'label' => __('admin.financials') !== 'admin.financials' ? __('admin.financials') : 'Financials',
            'route' => 'admin.projects.steps.financials',
        ],
        6 => [
            'label' => __('admin.property_models') !== 'admin.property_models' ? __('admin.property_models') : 'Models',
            'route' => 'admin.projects.steps.models',
        ],
        7 => [
            'label' => __('admin.settings') !== 'admin.settings' ? __('admin.settings') : 'Settings',
            'route' => 'admin.projects.steps.settings',
        ],
    ];
@endphp

<div class="w-full py-4 mb-6">
    <div class="flex items-center justify-between">
        @foreach($steps as $stepNum => $step)
            @php
                $isCompleted = $stepNum < $currentStep;
                $isCurrent = $stepNum == $currentStep;
                $routeExists = Route::has($step['route']);
                // Allow navigation if project exists and route exists
                $isClickable = $projectId && $routeExists;
                $url = $isClickable ? route($step['route'], $projectId) : '#';
            @endphp

            <div class="relative flex flex-col items-center flex-1">
                {{-- Connector Line (Right side) --}}
                @if(!$loop->last)
                    <div class="absolute top-[15px] left-[50%] right-[-50%] h-[2px] bg-gray-200 dark:bg-gray-700 -z-10">
                        <div class="h-full {{ $isCompleted ? 'bg-indigo-600' : 'bg-transparent' }}"></div>
                    </div>
                @endif

                <a href="{{ $url }}"
                   class="w-8 h-8 flex items-center justify-center rounded-full border-2
                          {{ $isCompleted || $isCurrent ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-gray-300 bg-white text-gray-500 dark:bg-gray-800 dark:border-gray-600' }}
                          {{ $isClickable ? 'cursor-pointer hover:bg-indigo-700 hover:border-indigo-700' : 'cursor-default pointer-events-none' }}
                          transition-colors duration-200 z-10"
                   title="{{ $step['label'] }}">
                    @if($isCompleted)
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    @else
                        <span class="text-xs font-bold">{{ $stepNum }}</span>
                    @endif
                </a>

                <span class="mt-2 text-xs font-medium {{ $isCurrent ? 'text-indigo-600' : 'text-gray-500 dark:text-gray-400' }} text-center hidden sm:block">
                    {{ $step['label'] }}
                </span>
            </div>
        @endforeach
    </div>
</div>
