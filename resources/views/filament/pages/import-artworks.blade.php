<x-filament-panels::page>
    <div class="space-y-6" @if ($batchId) wire:poll.5s @endif>
        {{ $this->content }}

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
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Обработка в фоновой очереди…</p>
                @endif
            </div>
        @endif

        <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 text-sm text-gray-600 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
            Альтернатива: скопируйте файлы в <code>storage/app/import-artworks</code> и нажмите «Импортировать из папки».
        </div>
    </div>
</x-filament-panels::page>
