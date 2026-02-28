@extends('adminlte::page')

@section('title', 'Announcement Details')

@section('content_header')
    <h1>Announcement Details</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Announcement #{{ $announcement->id }}</h3>
            <a href="{{ route('admin.announcements.index') }}" class="btn btn-default">
                <i class="fas fa-arrow-left"></i> Back to Announcements
            </a>
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Title</dt>
                <dd class="col-sm-9">{{ $announcement->title }}</dd>

                <dt class="col-sm-3">Type</dt>
                <dd class="col-sm-9">
                    @php
                        $badgeColor = match($announcement->type) {
                            'general' => 'primary',
                            'technical' => 'success',
                            'opportunity' => 'warning',
                            default => 'secondary'
                        };
                    @endphp
                    <span class="badge badge-{{ $badgeColor }}">
                        {{ ucfirst($announcement->type) }}
                    </span>
                </dd>

                <dt class="col-sm-3">Description</dt>
                <dd class="col-sm-9">{{ $announcement->description }}</dd>

                <dt class="col-sm-3">Link</dt>
                <dd class="col-sm-9">
                    @if($announcement->link)
                        <a href="{{ $announcement->link }}" target="_blank" rel="noopener noreferrer">
                            {{ $announcement->link }}
                        </a>
                    @else
                        <span class="text-muted">N/A</span>
                    @endif
                </dd>

                <dt class="col-sm-3">Starts At</dt>
                <dd class="col-sm-9">
                    {{ $announcement->starts_at ? $announcement->starts_at->format('Y-m-d H:i') : 'N/A' }}
                </dd>

                <dt class="col-sm-3">Ends At</dt>
                <dd class="col-sm-9">
                    {{ $announcement->ends_at ? $announcement->ends_at->format('Y-m-d H:i') : 'N/A' }}
                </dd>

                <dt class="col-sm-3">Created By</dt>
                <dd class="col-sm-9">
                    {{ optional($announcement->creator)->username ?? 'N/A' }}
                </dd>

                <dt class="col-sm-3">Created At</dt>
                <dd class="col-sm-9">{{ $announcement->created_at->format('Y-m-d H:i') }}</dd>
            </dl>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <div>
                @can('update', $announcement)
                    <a href="{{ route('admin.announcements.edit', $announcement) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                @endcan
            </div>
            <div>
                @can('delete', $announcement)
                    <form action="{{ route('admin.announcements.destroy', $announcement) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this announcement?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                @endcan
            </div>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop


