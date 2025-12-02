@extends('admin.layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold mb-4">@lang('admin.dashboard')</h3>
                    <p>@lang('admin.welcome_admin_panel')</p>
                </div>
            </div>
        </div>
    </div>
@endsection
