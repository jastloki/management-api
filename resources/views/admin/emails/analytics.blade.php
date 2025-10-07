@extends('admin.layouts.app')

@section('title', 'Email Analytics')
@section('heading', 'Email Analytics')



@section('content')
<!-- Status Distribution Chart -->
<div class="row mb-4">
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 fw-bold text-primary">Daily Email Sending Statistics (Last 30 Days)</h6>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="dailyEmailChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 fw-bold text-primary">Email Status Distribution</h6>
            </div>
            <div class="card-body">
                <div class="chart-pie pt-4 pb-2">
                    <canvas id="statusPieChart"></canvas>
                </div>
                <div class="mt-4 text-center small">
                    @foreach($statusDistribution as $status)
                        <span class="me-2">
                            <i class="bi bi-circle-fill text-{{
                                $status->email_status == 'sent' ? 'success' :
                                ($status->email_status == 'failed' ? 'danger' :
                                ($status->email_status == 'pending' ? 'warning' :
                                ($status->email_status == 'queued' ? 'info' : 'secondary')))
                            }}"></i> {{ ucfirst($status->email_status) }} ({{ $status->count }})
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Summary -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-primary">Email Statistics Summary</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="border-start border-success border-4 p-3">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">
                                Total Sent (Last 30 Days)
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                {{ $dailyStats->sum('total') }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="border-start border-info border-4 p-3">
                            <div class="text-xs fw-bold text-info text-uppercase mb-1">
                                Average Per Day
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                {{ $dailyStats->count() > 0 ? round($dailyStats->sum('total') / 30, 1) : 0 }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="border-start border-warning border-4 p-3">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">
                                Peak Day
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                @php
                                    $peakDay = $dailyStats->sortByDesc('total')->first();
                                @endphp
                                {{ $peakDay ? $peakDay->total . ' emails' : 'N/A' }}
                            </div>
                            @if($peakDay)
                                <div class="text-xs text-muted">
                                    {{ \Carbon\Carbon::parse($peakDay->date)->format('M d, Y') }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="border-start border-danger border-4 p-3">
                            <div class="text-xs fw-bold text-danger text-uppercase mb-1">
                                Success Rate
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                @php
                                    $totalClients = $statusDistribution->sum('count');
                                    $sentClients = $statusDistribution->where('email_status', 'sent')->first();
                                    $successRate = $totalClients > 0 ? round(($sentClients->count ?? 0) / $totalClients * 100, 1) : 0;
                                @endphp
                                {{ $successRate }}%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Statistics Table -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-primary">Daily Breakdown (Last 30 Days)</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Emails Sent</th>
                                <th>Day of Week</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dailyStats->sortByDesc('date') as $stat)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($stat->date)->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge bg-success">{{ $stat->total }}</span>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($stat->date)->format('l') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">No email sending data available for the last 30 days.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Daily Email Chart
const dailyCtx = document.getElementById('dailyEmailChart').getContext('2d');
const dailyEmailChart = new Chart(dailyCtx, {
    type: 'line',
    data: {
        labels: [
            @foreach($dailyStats->sortBy('date') as $stat)
                '{{ \Carbon\Carbon::parse($stat->date)->format('M d') }}',
            @endforeach
        ],
        datasets: [{
            label: 'Emails Sent',
            data: [
                @foreach($dailyStats->sortBy('date') as $stat)
                    {{ $stat->total }},
                @endforeach
            ],
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

// Status Distribution Pie Chart
const statusCtx = document.getElementById('statusPieChart').getContext('2d');
const statusPieChart = new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: [
            @foreach($statusDistribution as $status)
                '{{ ucfirst($status->email_status) }}',
            @endforeach
        ],
        datasets: [{
            data: [
                @foreach($statusDistribution as $status)
                    {{ $status->count }},
                @endforeach
            ],
            backgroundColor: [
                @foreach($statusDistribution as $status)
                    @if($status->email_status == 'sent')
                        '#28a745',
                    @elseif($status->email_status == 'failed')
                        '#dc3545',
                    @elseif($status->email_status == 'pending')
                        '#ffc107',
                    @elseif($status->email_status == 'queued')
                        '#17a2b8',
                    @else
                        '#6c757d',
                    @endif
                @endforeach
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                display: false
            }
        }
    }
});
</script>
@endsection
