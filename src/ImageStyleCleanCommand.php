<?php

namespace GeoffTech\LaravelImageStyle;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageStyleCleanCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'img:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove older dynamic image styles.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $delete_time = now()->subDays(15)->getTimestamp();

        $disk = config('imagestyle.disk');
        $folder = config('imagestyle.folder');

        collect(Storage::disk($disk)->listContents($folder, true))
            ->filter(fn($file) => $file['type'] == 'file')
            ->filter(fn($file) => $file['lastModified'] < $delete_time)
            ->map(fn($file) => $file['path'])
            ->each(function ($file) {
                Log::info('cleaned ' . $file);
                Storage::disk('cache')->delete($file);
            });
    }
}
