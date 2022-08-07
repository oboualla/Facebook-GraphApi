<?php

namespace App\Console;

use FbGraphApi;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;
use App\Mail\InformPosts;
use Illuminate\Support\Facades\Mail;

// require '/app/facebook_graph_api/index.php';

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */

    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            $limit = 0;
            $tasks = [];
            while (count($tasks) == 100 || $limit == 0) {
                $tasks = DB::select('select * from `scheduled_jobs` where `status`=? AND `pushing_date` <= current_timestamp limit ?, 200', ['pending', $limit]);
                foreach ($tasks as $task) {
                    $graphApi = new FbGraphApi($task->user_id, $task->access_token, 0);
                    $res = $graphApi->CreatePagePost($task->page_id, $task->access_token, $task->message);
                    $success = isset($res['id']) ? true : false;
                    $res = DB::update(
                        'update `scheduled_jobs` set `status`=? where `id`=? limit 1',
                        [$success ? 'success' : 'error', $task->id]
                    );
                    Mail::to($_SESSION['email'])->send(new InformPosts([
                        'name' => $task->name,
                        'status' => $success ? 'success' : 'error',
                        'page_id' => $task->page_id
                    ]));
                }
                $limit += 1;
                sleep(1);
            }
        })->everyMinute();
        // $schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
