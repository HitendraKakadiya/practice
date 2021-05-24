<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\storecard;
use Carbon\Carbon;
use App\User;
use Benwilkins\FCM\FcmMessage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Notification;
use App\Notifications\DateNotification;

class ExpDateNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ExpDateNotification:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     *
     * @return int
     */
    public function handle()
    {
        $card = storecard::with('user')->get();
        $date1 = Carbon::now()->toDateString();
        $card->map(function ($item) {
            $date2 = $item['expdate'];

            $date1_ts = strtotime('2021-05-20');
            $date2_ts = strtotime('2021-05-23');
            $diff = $date2_ts - $date1_ts;
            $days = round($diff / 86400);

            if ($days == 3) {
                $notification = Notification::send(
                    $item,
                    new DateNotification()
                );
                \Log::info($item);
            }
        });
    }
}
