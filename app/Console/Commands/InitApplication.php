<?php

namespace App\Console\Commands;

use App\Models\District;
use App\Models\Province;
use App\Models\Ward;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class InitApplication extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:init-application';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Khơi tạo ứng dụng với các thiết lập ban đầu';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $migrateCode = Artisan::call('migrate');
        $this->info('--- Khởi tạo database');


        if ($migrateCode === Command::SUCCESS) {
            $this->info('Lệnh migrate đã thành công!');
        } else {
            $this->error('Lỗi khi chạy migrate!');
            return Command::FAILURE;
        }

        $this->info('--- Seeding demo database');
        DB::beginTransaction();
        DB::commit();
        $this->info('Seeding database thành công');
        return Command::SUCCESS;
    }
}
