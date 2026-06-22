<x-mail::message>
# Новая заявка

**Работа:** {{ $orderRequest->artwork->title }}

**Раздел:** {{ $orderRequest->artwork->category?->name ?? 'Не указан' }}

**Цена:** {{ $orderRequest->artwork->formattedPrice() ?? 'Не указана' }}

**Имя:** {{ $orderRequest->customer_name }}

**Email:** {{ $orderRequest->customer_email ?? 'Не указан' }}

**Телефон:** {{ $orderRequest->customer_phone ?? 'Не указан' }}

**Сообщение:**

{{ $orderRequest->message ?: 'Без сообщения' }}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
