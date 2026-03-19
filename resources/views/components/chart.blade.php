@props([
    'id',
    'type' => 'bar',
    'data' => '{}',
    'options' => '{}',
    'height' => '300px',
])

<div style="position: relative; height: {{ $height }};">
    <canvas id="{{ $id }}" x-data x-init="
        const ctx = document.getElementById('{{ $id }}').getContext('2d');
        new Chart(ctx, {
            type: '{{ $type }}',
            data: {{ $data }},
            options: {{ $options }},
        });
    "></canvas>
</div>
