@extends('admin.layouts.app')

@section('header')
    @lang('admin.dashboard')
@endsection

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Row 1: KPI1 (Large Card 2x width), KPI2 Small, KPI3 Small -->

    <!-- KPI 1 (Large 2x) -->
    <div class="card col-span-1 md:col-span-2 flex flex-col justify-between relative overflow-hidden group">
        <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:scale-110 transition-transform duration-500">
            <i class="bi bi-wallet2 text-9xl text-primary-500"></i>
        </div>
        <div>
            <h4 class="text-text-secondary text-sm font-medium uppercase tracking-wider mb-1">Total Revenue</h4>
            <div class="text-3xl font-bold text-text-primary mb-2">$124,500.00</div>
            <div class="flex items-center text-sm">
                <span class="badge badge-success flex items-center gap-1">
                    <i class="bi bi-arrow-up-short"></i> 12%
                </span>
                <span class="text-text-secondary ml-2 rtl:mr-2">vs last month</span>
            </div>
        </div>
    </div>

    <!-- KPI 2 (Small) -->
    <div class="card flex flex-col justify-between">
        <div>
            <div class="flex justify-between items-start mb-2">
                <h4 class="text-text-secondary text-sm font-medium uppercase tracking-wider">Active Projects</h4>
                <div class="p-2 bg-primary-50 rounded-lg text-primary-600">
                    <i class="bi bi-building"></i>
                </div>
            </div>
            <div class="text-2xl font-bold text-text-primary">24</div>
        </div>
        <div class="mt-4 text-sm text-text-secondary">
            <span class="text-success-text font-medium">+3</span> new this month
        </div>
    </div>

    <!-- KPI 3 (Small) -->
    <div class="card flex flex-col justify-between">
        <div>
            <div class="flex justify-between items-start mb-2">
                <h4 class="text-text-secondary text-sm font-medium uppercase tracking-wider">Total Leads</h4>
                <div class="p-2 bg-info-bg rounded-lg text-info-text">
                    <i class="bi bi-people"></i>
                </div>
            </div>
            <div class="text-2xl font-bold text-text-primary">1,204</div>
        </div>
        <div class="mt-4 text-sm text-text-secondary">
            <span class="text-error-text font-medium">-2%</span> vs last week
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Row 2: Revenue Chart (Large, 2x width), Amenity Stats (New) -->

    <!-- Revenue Chart Placeholder -->
    <div class="card lg:col-span-2 min-h-[300px] flex flex-col">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-bold text-text-primary">Revenue Analytics</h3>
            <select class="form-select w-auto py-1 px-3 text-xs">
                <option>This Year</option>
                <option>Last Year</option>
            </select>
        </div>
        <div class="flex-1 bg-primary-50 rounded-[var(--radius-input)] flex items-center justify-center border border-dashed border-primary-100 text-primary-500">
            <!-- Placeholder for Chart -->
            <div class="text-center">
                <i class="bi bi-bar-chart-line text-4xl mb-2"></i>
                <p>Chart Component Placeholder</p>
            </div>
        </div>
    </div>

    <!-- Quick Actions (Moved below or replaced if needed, but keeping it per user instruction to add) -->
    <!-- Replacing Quick Actions with Amenities for this row, or adding a new row?
         The design has 3 columns. KPI took full width.
         Let's keep Quick Actions and Add Amenities in a new row or replace Quick Actions.
         The prompt says "Integrate cleanly".
         Quick Actions is useful. Let's move Quick Actions to a new row or modify the grid.
         Actually, let's put Amenities in the 3rd column here instead of Quick Actions for visibility,
         or move Quick Actions to the next row with Recent Projects.

         Decision: Replace Quick Actions in this grid with Top Amenities, move Quick Actions down or keep it.
         Wait, user said "Add basic analytics... integrate cleanly".

         Let's add a new card for Top Amenities.
         If I put it in the 3rd column of Row 2, it fits perfectly.
         Quick Actions can be moved to Row 3 (sidebar style) or kept.
         The Quick Actions card is useful.

         Let's add a new Row or Column.

         Option 1: Add to Row 2 (making it 4 cols? No, space issues).
         Option 2: Add below Row 2.

         Let's replace the Quick Actions slot with "Top Amenities" and move Quick Actions to be a smaller widget or merge.
         Actually, "Top Amenities" is analytics, "Revenue Analytics" is analytics. They go well together.

         Let's keep Quick Actions, and add Top Amenities as a new card in a new row or sidebar.

         Let's look at the layout again.
         Row 1: KPIs
         Row 2: Chart (2/3) + Quick Actions (1/3)
         Row 3: Recent Projects (1/2) + Recent Listings (1/2)
         Row 4: System Overview (1/1)

         I will add "Top Amenities" to the right of "Recent Listings" if I make it 3 cols, or add a new row.
         Let's make Row 3 have 3 columns? Or just add it as a card.

         Let's simply add a new card in the layout.
         Maybe below Quick Actions?

         Let's try putting it in place of "Quick Actions" for now to test, OR split the Quick Actions column.

         Actually, the user prompt showed a "Card" example.

         <div class="card">...</div>

         I will add it as a new column in Row 2 if space permits, or swap it.
         I'll move Quick Actions to a new Row 3, combined with something else, or just insert Top Amenities in the grid.

         Let's look at Row 3: It has 2 columns (Projects, Listings).
         I can make Row 3 have 3 columns: Projects, Listings, Top Amenities.
    -->

    <!-- Top Amenities Widget -->
    <div class="card h-full">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-text-primary">@lang('admin.top_project_amenities')</h3>
        </div>

        @if(isset($topProjectAmenities) && $topProjectAmenities->isNotEmpty())
            <ul class="space-y-3">
                @foreach($topProjectAmenities as $amenity)
                <li class="flex items-center justify-between p-2 hover:bg-gray-50 rounded transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-primary-50 text-primary-600 flex items-center justify-center">
                            @if($amenity->icon_class)
                                <i class="{{ $amenity->icon_class }}"></i>
                            @else
                                <i class="bi bi-star"></i>
                            @endif
                        </div>
                        <span class="text-sm font-medium text-text-primary">{{ $amenity->name }}</span>
                    </div>
                    <span class="badge badge-primary">{{ $amenity->projects_count }}</span>
                </li>
                @endforeach
            </ul>
        @else
            <div class="text-center py-8 text-text-secondary text-sm">
                @lang('admin.no_data_available')
            </div>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Row 3: Quick Actions, Active Projects, Recent Listings -->

    <!-- Quick Actions (Moved here) -->
    <div class="card">
        <h3 class="text-lg font-bold text-text-primary mb-4">@lang('admin.actions')</h3>
        <div class="space-y-3">
            <a href="{{ route('admin.projects.create') }}" class="group flex items-center p-3 rounded-[var(--radius-button)] hover:bg-primary-50 transition-colors border border-transparent hover:border-primary-100">
                <div class="w-10 h-10 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center group-hover:bg-primary-500 group-hover:text-white transition-colors">
                    <i class="bi bi-plus-lg"></i>
                </div>
                <div class="ml-3 rtl:mr-3">
                    <div class="font-semibold text-text-primary">Add New Project</div>
                    <div class="text-xs text-text-secondary">Create a new real estate project</div>
                </div>
            </a>

            <a href="#" class="group flex items-center p-3 rounded-[var(--radius-button)] hover:bg-info-bg transition-colors border border-transparent hover:border-blue-100">
                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center group-hover:bg-blue-500 group-hover:text-white transition-colors">
                    <i class="bi bi-person-plus"></i>
                </div>
                <div class="ml-3 rtl:mr-3">
                    <div class="font-semibold text-text-primary">Add Developer</div>
                    <div class="text-xs text-text-secondary">Register a new developer</div>
                </div>
            </a>

            <a href="#" class="group flex items-center p-3 rounded-[var(--radius-button)] hover:bg-success-bg transition-colors border border-transparent hover:border-green-100">
                <div class="w-10 h-10 rounded-full bg-green-100 text-green-600 flex items-center justify-center group-hover:bg-green-500 group-hover:text-white transition-colors">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <div class="ml-3 rtl:mr-3">
                    <div class="font-semibold text-text-primary">Manage Files</div>
                    <div class="text-xs text-text-secondary">Go to Media Manager</div>
                </div>
            </a>
        </div>
    </div>

    <!-- Active Projects -->
    <div class="card">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-text-primary">Recent Projects</h3>
            <a href="{{ route('admin.projects.index') }}" class="text-sm text-primary-600 hover:text-primary-500 font-medium">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left rtl:text-right">
                <thead>
                    <tr class="text-text-secondary text-xs uppercase border-b border-[var(--color-divider)]">
                        <th class="pb-3 font-medium">Project Name</th>
                        <th class="pb-3 font-medium">Status</th>
                        <th class="pb-3 font-medium text-right rtl:text-left">Price</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <!-- Dummy Data -->
                    <tr class="group hover:bg-gray-50 transition-colors">
                        <td class="py-3 text-text-primary font-medium">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded bg-gray-200"></div> <!-- Placeholder Img -->
                                <span>Skyline Towers</span>
                            </div>
                        </td>
                        <td class="py-3"><span class="badge badge-success">Ready</span></td>
                        <td class="py-3 text-right rtl:text-left font-medium">$450k</td>
                    </tr>
                    <tr class="group hover:bg-gray-50 transition-colors">
                        <td class="py-3 text-text-primary font-medium">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded bg-gray-200"></div> <!-- Placeholder Img -->
                                <span>Oasis Garden</span>
                            </div>
                        </td>
                        <td class="py-3"><span class="badge badge-warning">Under Construction</span></td>
                        <td class="py-3 text-right rtl:text-left font-medium">$280k</td>
                    </tr>
                    <tr class="group hover:bg-gray-50 transition-colors">
                        <td class="py-3 text-text-primary font-medium">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded bg-gray-200"></div> <!-- Placeholder Img -->
                                <span>Blue Horizon</span>
                            </div>
                        </td>
                        <td class="py-3"><span class="badge badge-info">Off Plan</span></td>
                        <td class="py-3 text-right rtl:text-left font-medium">$320k</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Listings -->
    <div class="card">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-text-primary">Recent Listings</h3>
             <a href="{{ route('admin.listings.index') }}" class="text-sm text-primary-600 hover:text-primary-500 font-medium">View All</a>
        </div>
         <div class="space-y-4">
            <!-- Item 1 -->
            <div class="flex items-center p-3 border border-[var(--color-border-soft)] rounded-[var(--radius-button)] hover:border-primary-100 hover:bg-primary-50 transition-all cursor-pointer">
                <div class="w-12 h-12 rounded-[10px] bg-gray-200 flex-shrink-0"></div>
                <div class="ml-3 rtl:mr-3 flex-1">
                    <h5 class="text-sm font-bold text-text-primary">Luxury Penthouse</h5>
                    <p class="text-xs text-text-secondary">Downtown, Dubai</p>
                </div>
                <div class="text-right rtl:text-left">
                     <span class="block text-sm font-bold text-primary-600">$1.2M</span>
                     <span class="text-[10px] text-text-secondary">2 mins ago</span>
                </div>
            </div>
             <!-- Item 2 -->
            <div class="flex items-center p-3 border border-[var(--color-border-soft)] rounded-[var(--radius-button)] hover:border-primary-100 hover:bg-primary-50 transition-all cursor-pointer">
                <div class="w-12 h-12 rounded-[10px] bg-gray-200 flex-shrink-0"></div>
                <div class="ml-3 rtl:mr-3 flex-1">
                    <h5 class="text-sm font-bold text-text-primary">Modern Villa</h5>
                    <p class="text-xs text-text-secondary">Palm Jumeirah</p>
                </div>
                <div class="text-right rtl:text-left">
                     <span class="block text-sm font-bold text-primary-600">$5.5M</span>
                     <span class="text-[10px] text-text-secondary">1 hour ago</span>
                </div>
            </div>
             <!-- Item 3 -->
            <div class="flex items-center p-3 border border-[var(--color-border-soft)] rounded-[var(--radius-button)] hover:border-primary-100 hover:bg-primary-50 transition-all cursor-pointer">
                <div class="w-12 h-12 rounded-[10px] bg-gray-200 flex-shrink-0"></div>
                <div class="ml-3 rtl:mr-3 flex-1">
                    <h5 class="text-sm font-bold text-text-primary">Studio Apartment</h5>
                    <p class="text-xs text-text-secondary">Business Bay</p>
                </div>
                <div class="text-right rtl:text-left">
                     <span class="block text-sm font-bold text-primary-600">$180k</span>
                     <span class="text-[10px] text-text-secondary">3 hours ago</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 mb-8">
    <!-- Row 4: System Overview (Full width) -->
    <div class="card">
        <h3 class="text-lg font-bold text-text-primary mb-4">System Overview</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
             <div class="p-4 rounded-[var(--radius-input)] bg-gray-50 border border-dashed border-gray-200 text-center">
                 <div class="text-3xl font-bold text-text-primary mb-1">98%</div>
                 <div class="text-xs text-text-secondary uppercase tracking-wider">Uptime</div>
             </div>
             <div class="p-4 rounded-[var(--radius-input)] bg-gray-50 border border-dashed border-gray-200 text-center">
                 <div class="text-3xl font-bold text-text-primary mb-1">1.2s</div>
                 <div class="text-xs text-text-secondary uppercase tracking-wider">Avg Load Time</div>
             </div>
             <div class="p-4 rounded-[var(--radius-input)] bg-gray-50 border border-dashed border-gray-200 text-center">
                 <div class="text-3xl font-bold text-text-primary mb-1">5</div>
                 <div class="text-xs text-text-secondary uppercase tracking-wider">Pending Approvals</div>
             </div>
             <div class="p-4 rounded-[var(--radius-input)] bg-gray-50 border border-dashed border-gray-200 text-center">
                 <div class="text-3xl font-bold text-text-primary mb-1">12</div>
                 <div class="text-xs text-text-secondary uppercase tracking-wider">New Users</div>
             </div>
        </div>
    </div>
</div>
@endsection
