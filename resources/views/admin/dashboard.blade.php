@extends('admin.layouts.app')

@section('header', __('admin.dashboard'))

@section('content')
    <x-page-header :title="__('admin.dashboard')" :subtitle="__('Welcome to the AqarAnd operations center')">
        <x-slot name="actions">
            <x-button variant="primary" href="{{ route('admin.listings.index') }}">
                <i class="bi bi-plus-lg"></i>
                {{ __('admin.create_new') }}
            </x-button>
        </x-slot>
    </x-page-header>

    <x-bento-grid>
        <x-dashboard-card :title="__('admin.dashboard')" :subtitle="__('Welcome to the AqarAnd operations center')" meta="AqarAnd">
            <p class="card-subtitle">{{ __('Modernized UI kit with RTL-first styling and fluid spacing.') }}</p>
            <div class="tabs" aria-label="Design tokens">
                <button type="button" class="tab-button is-active">RTL</button>
                <button type="button" class="tab-button">LTR</button>
                <button type="button" class="tab-button">Responsive</button>
            </div>
        </x-dashboard-card>

        <x-dashboard-card :title="__('admin.common_info')" :subtitle="__('admin.app_name')">
            <div class="page-grid">
                <x-badge variant="primary">{{ __('Neutral palette applied') }}</x-badge>
                <x-badge variant="success">{{ __('Soft shadows enabled') }}</x-badge>
                <x-badge variant="warning">{{ __('Superellipse corners') }}</x-badge>
                <x-badge variant="info">{{ __('Adaptive typography') }}</x-badge>
            </div>
        </x-dashboard-card>

        <x-dashboard-card :title="__('admin.actions')" :subtitle="__('admin.status')">
            <div class="table-wrapper">
                <x-data-table :headers="[__('admin.actions'), __('admin.status')]" :rows="[[__('admin.create_new'), __('admin.active')], [__('admin.update'), __('admin.inactive')]]" />
            </div>
        </x-dashboard-card>
    </x-bento-grid>
@endsection
