@extends('admin.layouts.app')

@section('title', __('admin.view_country'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('admin.view_country') }}: {{ $country->getName() }}</h3>
                    <div class="card-tools">
                        <a href="{{ route($adminRoutePrefix.'countries.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> {{ __('admin.back') }}
                        </a>
                        <a href="{{ route($adminRoutePrefix.'countries.edit', $country) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> {{ __('admin.edit') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>{{ __('admin.name_en') }}</label>
                                <p class="form-control-static">{{ $country->name_en }}</p>
                            </div>
                            <div class="form-group mb-3">
                                <label>{{ __('admin.name_ar') }}</label>
                                <p class="form-control-static">{{ $country->name_local }}</p>
                            </div>
                            <div class="form-group mb-3">
                                <label>{{ __('admin.code') }}</label>
                                <p class="form-control-static">{{ $country->code }}</p>
                            </div>
                            <div class="form-group mb-3">
                                <label>{{ __('admin.status') }}</label>
                                <p class="form-control-static">
                                    @if($country->is_active)
                                        <span class="badge bg-success">{{ __('admin.active') }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ __('admin.inactive') }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            @include('admin.partials.map', [
                                'lat' => $country->lat,
                                'lng' => $country->lng,
                                'readOnly' => true
                            ])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
