@php
    $delivery = $getRecord();
    $prescriptions = $delivery?->prescriptions ?? collect();
@endphp

@include('filament.resources.package-deliveries.prescriptions-modal', [
    'delivery' => $delivery,
    'prescriptions' => $prescriptions,
])
