<!DOCTYPE html>
<html>
<head>
    <title>Отчет по заказу #{{ $order->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-8">
<div class="max-w-3xl mx-auto bg-white rounded-lg shadow-lg p-6">
    <h1 class="text-2xl font-bold mb-6">Отчет по заказу #{{ $order->id }}</h1>

    <!-- Блок статуса -->
    <div class="mb-6 p-4 rounded-lg
            @if($order->status == 'done') bg-green-100
            @elseif($order->status == 'process') bg-orange-100
            @else bg-yellow-100 @endif">
        Статус: {{ trans("statuses.$order->status") }}
    </div>

    <!-- Клиент и модель -->
    <div class="grid grid-cols-2 gap-4 mb-8">
        <div>
            <h2 class="font-semibold">Клиент:</h2>
            <p>{{ $order->client->name }}</p>
            <p>{{ $order->client->phone }}</p>
        </div>
        <div>
            <h2 class="font-semibold">Модель оборудования:</h2>
            <p>{{ $order->model->name ?? 'Не указана' }}</p>
        </div>
    </div>

    <!-- Таблицы с услугами и материалами -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4">Выполненные работы</h2>
        <table class="w-full mb-6">
            @foreach($order->services as $service)
                <tr class="border-b">
                    <td class="py-2">{{ $service->service }}</td>
                    <td class="text-right">{{ number_format($service->price, 2) }} ₽</td>
                </tr>
            @endforeach
        </table>
    </div>

    <!-- Кнопка для PDF -->
    <a href="{{ route('order.pdf', $order->share_token) }}"
       class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
        Скачать PDF
    </a>
</div>
</body>
</html>
