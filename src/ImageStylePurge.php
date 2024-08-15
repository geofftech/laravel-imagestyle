<?php

namespace GeoffTech\LaravelImageStyle;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageStylePurge extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'img:purge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove all dynamic image styles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $disk = config('imagestyle.disk');
        $folder = config('imagestyle.folder');

        collect(Storage::disk($disk)->allFiles($folder))
            ->each(function ($file) {
                Log::info('purged ' . $file);
                Storage::disk('cache')->delete($file);
            });
    }
}
