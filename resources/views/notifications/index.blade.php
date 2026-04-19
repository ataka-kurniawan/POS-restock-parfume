@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Pusat Notifikasi</h3>
</div>

<div class="card shadow">
    <div class="card-body p-0">
        <ul class="list-group list-group-flush">
            @forelse($notifications as $notif)
            <li class="list-group-item {{ $notif->is_read ? 'bg-light' : '' }} d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1 {{ $notif->is_read ? 'text-muted' : 'fw-bold' }}">
                        @if($notif->type == 'out_of_stock')
                            <i class="fas fa-times-circle text-danger me-2"></i>
                        @else
                            <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        @endif
                        {{ $notif->title }}
                    </h6>
                    <p class="mb-0 small text-muted">{{ $notif->message }}</p>
                    <small class="text-secondary">{{ $notif->created_at->diffForHumans() }}</small>
                </div>
                @if(!$notif->is_read)
                <form action="{{ route('notifications.read', $notif->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-primary">Tandai Dibaca</button>
                </form>
                @endif
            </li>
            @empty
            <li class="list-group-item text-center py-4">Tidak ada notifikasi baru.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection