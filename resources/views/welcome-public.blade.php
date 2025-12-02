@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h1 class="text-3xl font-bold mb-4 text-center text-blue-600">
                    Aqar-and
                </h1>
                <p class="text-center text-gray-600 text-xl">
                    Public site coming soon.
                </p>
                <div class="mt-8 text-center">
                    @auth
                        @if(Auth::user()->is_admin)
                            <a href="{{ route('admin.dashboard') }}" class="text-blue-500 hover:underline">Go to Admin Dashboard</a>
                        @else
                            <a href="{{ route('dashboard') }}" class="text-blue-500 hover:underline">Go to Dashboard</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="text-blue-500 hover:underline">Login</a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
@endsection
