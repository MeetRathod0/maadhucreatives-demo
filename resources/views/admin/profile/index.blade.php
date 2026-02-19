@extends('admin.layouts.app')

@section('title', 'Profile')
@section('page-title', 'Profile')

@section('breadcrumbs')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.dashboard') }}">Home</a>
    </li>
    <li class="breadcrumb-item active">Profile</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">

        <!-- Admin Info Card -->
        <div class="card card-primary card-outline mb-4">
            <div class="card-body text-center">
                <i class="fas fa-user-circle fa-5x text-secondary mb-3"></i>
                <h5 class="mb-1">{{ $admin->name }}</h5>
                <p class="text-muted mb-0">{{ $admin->email }}</p>
            </div>
        </div>

        <!-- Change Password Card -->
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-key mr-2"></i>Change Password
                </h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.profile.password') }}" method="POST">
                    @csrf

                    <!-- Current Password -->
                    <div class="form-group">
                        <label for="old_password">Current Password <span class="text-danger">*</span></label>
                        <input type="password"
                               name="old_password"
                               id="old_password"
                               class="form-control @error('old_password') is-invalid @enderror"
                               placeholder="Enter current password">
                        @error('old_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- New Password -->
                    <div class="form-group">
                        <label for="password">New Password <span class="text-danger">*</span></label>
                        <input type="password"
                               name="password"
                               id="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Enter new password (min 8 characters)">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Confirm New Password -->
                    <div class="form-group">
                        <label for="password_confirmation">Confirm New Password <span class="text-danger">*</span></label>
                        <input type="password"
                               name="password_confirmation"
                               id="password_confirmation"
                               class="form-control"
                               placeholder="Re-enter new password">
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-save mr-2"></i>Update Password
                    </button>

                </form>
            </div>
        </div>

    </div>
</div>
@endsection
