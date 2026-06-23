<?php

namespace Tests\Feature;

use App\Jobs\ProcessImportArtworkJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ImportPendingArtworksCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_dispatches_import_when_files_exist_and_queue_is_idle(): void
    {
        Bus::fake();

        $path = storage_path('framework/testing/import-pending-command');
        File::ensureDirectoryExists($path);
        File::put($path.'/work-one.jpg', 'fake image');

        config(['gallery.import_path' => $path]);

        $this->artisan('gallery:import-pending')
            ->assertSuccessful()
            ->expectsOutputToContain('В очередь добавлено: 1 изображений.');

        Bus::assertBatched(fn ($batch): bool => $batch->name === 'import-artworks');

        File::deleteDirectory($path);
    }

    public function test_command_skips_when_import_jobs_are_already_queued(): void
    {
        Bus::fake();

        $path = storage_path('framework/testing/import-pending-command-busy');
        File::ensureDirectoryExists($path);
        File::put($path.'/work-two.jpg', 'fake image');

        config(['gallery.import_path' => $path]);

        DB::table('jobs')->insert([
            'queue' => 'default',
            'payload' => json_encode([
                'displayName' => ProcessImportArtworkJob::class,
                'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
                'data' => [
                    'commandName' => ProcessImportArtworkJob::class,
                    'command' => serialize(new ProcessImportArtworkJob($path.'/work-two.jpg')),
                ],
            ]),
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => now()->timestamp,
            'created_at' => now()->timestamp,
        ]);

        $this->artisan('gallery:import-pending')
            ->assertSuccessful()
            ->doesntExpectOutput('В очередь добавлено:');

        Bus::assertNothingBatched();

        File::deleteDirectory($path);
    }

    public function test_command_does_nothing_when_import_folder_is_empty(): void
    {
        Bus::fake();

        $path = storage_path('framework/testing/import-pending-command-empty');
        File::ensureDirectoryExists($path);

        config(['gallery.import_path' => $path]);

        $this->artisan('gallery:import-pending')
            ->assertSuccessful();

        Bus::assertNothingBatched();

        File::deleteDirectory($path);
    }
}
