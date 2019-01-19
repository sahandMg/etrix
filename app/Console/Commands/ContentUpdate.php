<?php

namespace App\Console\Commands;

use App\Brief;
use App\Jobs\TimeUpdate;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ContentUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'content:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updating content time';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *TODO add this line to crontab -e after starting supervisor service
     *  0 0 * * * php /var/www/html/ariaelec/artisan content:update
     * @return mixed
     */
    public function handle()
    {
        Brief::chunk(100, function ($briefs) {

            foreach ($briefs as $brief) {

                $brief->update(['days' => Carbon::now()->diffInDays($brief->created_at)]);
            }

        });

        Log::info('Contents are up to date:  ' . Carbon::now());
    }
}
