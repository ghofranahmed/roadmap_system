@extends('adminlte::page')

@section('title', 'Admin Dashboard')

@section('content_header')
    <h1>
        @if(auth()->user()->isNormalAdmin())
            Normal Admin Dashboard
        @elseif(auth()->user()->isTechAdmin())
            Technical Admin Dashboard
        @else
            Admin Dashboard
        @endif
    </h1>
    <p class="text-muted">
        @if(auth()->user()->isNormalAdmin())
            Manage users, announcements, and chat moderation
        @elseif(auth()->user()->isTechAdmin())
            Manage all content: roadmaps, units, lessons, quizzes, and challenges
        @else
            Welcome to the admin panel
        @endif
    </p>
@stop

@section('content')
    @if(auth()->user()->isNormalAdmin())
        {{-- Normal Admin Dashboard --}}
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $stats['total_users'] ?? 0 }}</h3>
                        <p>Total Users</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <a href="{{ route('admin.users.index') }}" class="small-box-footer">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $stats['total_admins'] ?? 0 }}</h3>
                        <p>Total Admins</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <a href="{{ route('admin.users.index') }}?role=admin" class="small-box-footer">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $stats['active_announcements'] ?? 0 }}</h3>
                        <p>Active Announcements</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <a href="{{ route('admin.announcements.index') }}" class="small-box-footer">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $stats['total_announcements'] ?? 0 }}</h3>
                        <p>Total Announcements</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-list"></i>
                    </div>
                    <a href="{{ route('admin.announcements.index') }}" class="small-box-footer">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- Notification Stats Row --}}
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>{{ $stats['total_notifications'] ?? 0 }}</h3>
                        <p>Total Notifications</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <a href="{{ route('admin.notifications.index') }}" class="small-box-footer">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-secondary">
                    <div class="inner">
                        <h3>{{ $stats['unread_notifications'] ?? 0 }}</h3>
                        <p>Unread Notifications</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <a href="{{ route('admin.notifications.index') }}?read_status=unread" class="small-box-footer">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Quick Actions</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <a href="{{ route('admin.users.index') }}" class="btn btn-primary btn-block">
                                    <i class="fas fa-users"></i> Manage Users
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('admin.announcements.create') }}" class="btn btn-success btn-block">
                                    <i class="fas fa-plus"></i> Create Announcement
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('admin.notifications.create') }}" class="btn btn-info btn-block">
                                    <i class="fas fa-bell"></i> Create Notification
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('admin.announcements.index') }}" class="btn btn-default btn-block">
                                    <i class="fas fa-bullhorn"></i> View Announcements
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @elseif(auth()->user()->isTechAdmin())
        {{-- Technical Admin Dashboard --}}
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $stats['total_roadmaps'] ?? 0 }}</h3>
                        <p>Total Roadmaps</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-map"></i>
                    </div>
                    <a href="{{ route('admin.roadmaps.index') }}" class="small-box-footer">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $stats['active_roadmaps'] ?? 0 }}</h3>
                        <p>Active Roadmaps</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <a href="{{ route('admin.roadmaps.index') }}?is_active=1" class="small-box-footer">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $stats['total_learning_units'] ?? 0 }}</h3>
                        <p>Learning Units</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <a href="{{ route('admin.learning-units.index') }}" class="small-box-footer">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $stats['total_lessons'] ?? 0 }}</h3>
                        <p>Total Lessons</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <a href="{{ route('admin.lessons.index') }}" class="small-box-footer">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>{{ $stats['total_quizzes'] ?? 0 }}</h3>
                        <p>Total Quizzes</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <a href="{{ route('admin.quizzes.index') }}" class="small-box-footer">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-secondary">
                    <div class="inner">
                        <h3>{{ $stats['total_challenges'] ?? 0 }}</h3>
                        <p>Total Challenges</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-code"></i>
                    </div>
                    <a href="{{ route('admin.challenges.index') }}" class="small-box-footer">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Quick Actions</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <a href="{{ route('admin.roadmaps.create') }}" class="btn btn-primary btn-block">
                                    <i class="fas fa-plus"></i> Create Roadmap
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('admin.learning-units.create') }}" class="btn btn-success btn-block">
                                    <i class="fas fa-plus"></i> Create Learning Unit
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('admin.quizzes.create') }}" class="btn btn-info btn-block">
                                    <i class="fas fa-plus"></i> Create Quiz
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('admin.create-admin') }}" class="btn btn-warning btn-block">
                                    <i class="fas fa-user-plus"></i> Create Admin
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@stop

@section('css')
@stop

@section('js')
@stop

