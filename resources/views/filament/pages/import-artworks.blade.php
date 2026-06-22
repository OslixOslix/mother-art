<x-filament-panels::page>
    <div class="space-y-6" @if ($batchId) wire:poll.5s @endif>
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Папка импорта</h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                Скопируйте изображения в <code>storage/app/import-artworks</code>. Поддерживаются jpg, jpeg, png, webp и gif.
                Каждое фото отправляется в OpenRouter для обработки: объект на белом фоне 1:1, готово для карточки товара.
                После обработки создаются неопубликованные черновики в разделе «Работы».
            </p>
            <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">
                Импорт выполняется в фоновой очереди — для пакетной загрузки (до ~100 файлов) должен работать воркер:
                <code>php artisan queue:work</code> или сервис <code>queue</code> в Docker.
            </p>
        </div>

        @if ($status = $this->batchStatus())
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Статус импорта</h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                    Обработано: {{ $status['processed'] }} из {{ $status['total'] }}
                    @if ($status['failed'] > 0)
                        <span class="text-danger-600 dark:text-danger-400">(ошибок: {{ $status['failed'] }})</span>
                    @endif
                </p>
                @if ($status['finished'])
                    <p class="mt-2 text-sm text-success-600 dark:text-success-400">
                        @if ($status['cancelled'])
                            Импорт отменён.
                        @elseif ($status['failed'] > 0)
                            Импорт завершён с ошибками. Необработанные файлы остались в папке импорта.
                        @else
                            Импорт успешно завершён.
                        @endif
                    </p>
                @else
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Ожидание воркера очереди…</p>
                @endif
            </div>
        @endif

        <x-filament::button wire:click="import">
            Импортировать фото
        </x-filament::button>
    </div>
</x-filament-panels::page>
