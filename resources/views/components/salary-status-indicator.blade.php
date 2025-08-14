@php
    $statusConfig = [
        'complete' => [
            'color' => 'green',
            'icon' => 'fas fa-check-circle',
            'text' => 'Lengkap',
            'bg' => 'bg-green-100',
            'text_color' => 'text-green-800'
        ],
        'partial' => [
            'color' => 'yellow',
            'icon' => 'fas fa-exclamation-triangle',
            'text' => 'Kurang',
            'bg' => 'bg-yellow-100',
            'text_color' => 'text-yellow-800'
        ],
        'empty' => [
            'color' => 'red',
            'icon' => 'fas fa-times-circle',
            'text' => 'Belum',
            'bg' => 'bg-red-100',
            'text_color' => 'text-red-800'
        ]
    ];
    
    // Handle case when status is null or empty
    if (!$status || !is_array($status)) {
        $status = [
            'input_days' => 0,
            'working_days' => 0,
            'percentage' => 0,
            'status' => 'empty',
            'last_input_date' => null
        ];
    }
    
    $config = $statusConfig[$status['status']] ?? $statusConfig['empty'];
@endphp

<div class="flex flex-col space-y-1">
    <div class="flex items-center space-x-2">
        <span class="text-sm font-medium text-gray-900">
            {{ $status['input_days'] ?? 0 }}/{{ $status['working_days'] ?? 0 }} - {{ $status['percentage'] ?? 0 }}%
        </span>
        <i class="{{ $config['icon'] }} text-{{ $config['color'] }}-500 text-sm"></i>
    </div>
    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $config['bg'] }} {{ $config['text_color'] }}">
        {{ $config['text'] }}
    </span>
    @if(isset($status['last_input_date']) && $status['last_input_date'])
        <div class="text-xs text-gray-500">
            Terakhir: {{ \Carbon\Carbon::parse($status['last_input_date'])->format('d/m/Y') }}
        </div>
    @endif
</div>