<!DOCTYPE html>
<html>
<head>
    <title>Отчет по заказу</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-8">
<div class="max-w-3xl mx-auto bg-white rounded-lg shadow-lg p-6">
    <h1 class="text-2xl font-bold mb-6">Отчет по заказу </h1>

{{--    <!-- Блок статуса -->--}}
{{--    <div class="mb-6 p-4 rounded-lg--}}
{{--            @if($order->status == 'done') bg-green-100--}}
{{--            @elseif($order->status == 'process') bg-orange-100--}}
{{--            @else bg-yellow-100 @endif">--}}
{{--        Статус: {{ trans("statuses.$order->status") }}--}}
{{--    </div>--}}

    @php
        $statusClasses = [
            'done' => 'bg-green-100 text-green-800',
            'process' => 'bg-red-100 text-orange-800',
            'waiting' => 'bg-yellow-100 text-yellow-800',
        ];

        $statusTexts = [
            'done' => 'Завершен',
            'process' => 'В процессе',
            'waiting' => 'В ожидании',
        ];

        $currentStatus = $order->status ?? 'new';
    @endphp

    <div class="mb-6 p-4 rounded-lg {{ $statusClasses[$currentStatus] ?? 'bg-gray-100 text-gray-800' }}">
        <strong>Статус заказа:</strong> {{ $statusTexts[$currentStatus] ?? ucfirst($currentStatus) }}
    </div>


    <!-- Клиент и модель -->
    <div class="grid grid-cols-2 gap-4 mb-8">
        <div>
            <h2 class="font-semibold">Клиент:</h2>
            <p>{{ $order->client->name }}</p>
            <p>{{ $order->client->phone }}</p>
        </div>
{{--        <div>--}}
{{--            <h2 class="font-semibold">Модель оборудования:</h2>--}}
{{--            <p>{{ $order->model_id ?? 'Не указана' }}</p>--}}
{{--        </div>--}}
    </div>

    <!-- Таблицы с услугами и материалами -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4">Выполненные работы и материалы:</h2>
        <table class="w-full mb-6">
            @foreach($order->services as $service)
                <tr class="border-b">
                    <td class="py-2">{{ $service->service }}</td>
                    <td class="text-right">{{ number_format($service->pivot->sale_price) }} ₽</td>
                </tr>
            @endforeach

            @foreach($order->products as $product)
                <tr class="border-b">
                    <td class="py-2">{{ $product->name }}</td>
                    <td class="text-right">{{number_format($product->base_sale_price)}} ₽</td>
                </tr>
            @endforeach

            @foreach($order->materials as $material)
                <tr class="border-b">
                    <td class="py-2">{{$material->name}}</td>
                    <td class="text-right">{{number_format($material->price)}} ₽</td>
                </tr>
            @endforeach

        </table>
        Всего к оплате: {{$order->total_amount}}₽
    </div>
{{--    {{dd($order->load('client', 'services', 'materials', 'products'))}}--}}
{{--    {{$order}}--}}

    <!-- Кнопка для PDF -->
{{--    <a href="{{ route('order.pdf', $order->share_token) }}"--}}
{{--       class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">--}}
{{--        Скачать PDF--}}
{{--    </a>--}}
</div>
</body>
</html>
