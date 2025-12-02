@extends('admin.layouts.app')

@section('title', __('admin.view_city'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('admin.view_city') }}: {{ $city->getName() }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.cities.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> {{ __('admin.back') }}
                        </a>
                        <a href="{{ route('admin.cities.edit', $city) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> {{ __('admin.edit') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>{{ __('admin.name_en') }}</label>
                                <p class="form-control-static">{{ $city->name_en }}</p>
                            </div>
                            <div class="form-group mb-3">
                                <label>{{ __('admin.name_ar') }}</label>
                                <p class="form-control-static">{{ $city->name_local }}</p>
                            </div>
                            <div class="form-group mb-3">
                                <label>{{ __('admin.region') }}</label>
                                <p class="form-control-static">{{ $city->region->getName() }}</p>
                            </div>
                            <div class="form-group mb-3">
                                <label>{{ __('admin.country') }}</label>
                                <p class="form-control-static">{{ $city->region->country->getName() }}</p>
                            </div>
                            <div class="form-group mb-3">
                                <label>{{ __('admin.status') }}</label>
                                <p class="form-control-static">
                                    @if($city->is_active)
                                        <span class="badge bg-success">{{ __('admin.active') }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ __('admin.inactive') }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            @include('admin.partials.map', [
                                'lat' => $city->lat,
                                'lng' => $city->lng,
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
