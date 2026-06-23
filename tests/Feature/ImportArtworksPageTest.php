<?php

namespace Tests\Feature;

use App\Filament\Pages\ImportArtworks;
use App\Models\User;
use App\Services\ArtworkImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ImportArtworksPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_from_folder_dispatches_batch(): void
    {
        Bus::fake();

        $user = User::factory()->create();
        $path = config('gallery.import_path');
        File::ensureDirectoryExists($path);
        File::put($path.'/ceramic-vase.jpg', 'fake image');

        Livewire::actingAs($user)
            ->test(ImportArtworks::class)
            ->call('import')
            ->assertSet('batchId', fn (?string $batchId): bool => filled($batchId));

        Bus::assertBatched(fn ($batch): bool => $batch->name === 'import-artworks');

        File::delete($path.'/ceramic-vase.jpg');
    }

    public function test_upload_and_import_without_files_shows_warning(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ImportArtworks::class)
            ->call('uploadAndImport')
            ->assertSet('batchId', null);
    }

    public function test_upload_and_import_with_files_dispatches_batch(): void
    {
        Bus::fake();

        $user = User::factory()->create();
        Storage::fake('import');
        Storage::disk('import')->put('ceramic-vase.jpg', 'fake image');

        Livewire::actingAs($user)
            ->test(ImportArtworks::class)
            ->fillForm([
                'photos' => ['ceramic-vase.jpg'],
            ])
            ->call('uploadAndImport')
            ->assertSet('batchId', fn (?string $batchId): bool => filled($batchId));

        Bus::assertBatched(fn ($batch): bool => $batch->name === 'import-artworks');
    }

    public function test_page_renders_form_submit_actions(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ImportArtworks::class)
            ->assertSee('Загрузить и импортировать')
            ->assertSee('Импортировать из папки');
    }

    public function test_count_images_in_import_path(): void
    {
        $path = storage_path('framework/testing/import-artworks-count');
        File::ensureDirectoryExists($path);
        File::put($path.'/one.jpg', 'x');
        File::put($path.'/two.png', 'x');
        File::put($path.'/readme.txt', 'x');

        $service = app(ArtworkImportService::class);

        $this->assertSame(2, $service->countImagesInPath($path));

        File::deleteDirectory($path);
    }

    public function test_pending_import_count_on_page(): void
    {
        $user = User::factory()->create();
        $path = config('gallery.import_path');
        File::ensureDirectoryExists($path);
        File::put($path.'/waiting.jpg', 'fake');

        Livewire::actingAs($user)
            ->test(ImportArtworks::class)
            ->assertSee('ожидает обработки')
            ->assertSee('1');

        File::delete($path.'/waiting.jpg');
    }
}
