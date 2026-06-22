<x-filament-panels::page>
    <div class="space-y-6">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Папка импорта</h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                Скопируйте изображения в <code>storage/app/import-artworks</code>. Поддерживаются jpg, jpeg, png, webp и gif.
                После импорта будут созданы неопубликованные черновики, которые можно отредактировать в разделе “Работы”.
            </p>
            <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">
                Файлы после импорта переносятся в public storage. Перед публикацией укажите раздел, название и цену.
            </p>
        </div>

        <x-filament::button wire:click="import">
            Импортировать фото
        </x-filament::button>
    </div>
</x-filament-panels::page>
