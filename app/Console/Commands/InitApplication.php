<?php

namespace App\Console\Commands;

use App\Models\District;
use App\Models\Province;
use App\Models\Ward;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\Bank;

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
    protected $description = 'Khởi tạo ứng dụng với các thiết lập ban đầu';

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
        try {
            // Lấy danh sách ngân hàng từ VietQR
            $this->info('Tải danh sách ngân hàng từ VietQR...');
            $res = Http::timeout(20)->get('https://api.vietqr.io/v2/banks');
            if ($res->ok()) {
                $data = $res->json('data') ?? [];
                foreach ($data as $item) {
                    $code = $item['code'] ?? null;
                    $name = $item['name'] ?? ($item['shortName'] ?? null);
                    if (! $code || ! $name) {
                        continue;
                    }
                    Bank::updateOrCreate(['code' => $code], ['name' => $name]);
                }
                $this->info('Seed ngân hàng: thành công');
            } else {
                $this->warn('Không thể tải danh sách ngân hàng từ VietQR. Bỏ qua.');
            }
        } catch (\Throwable $e) {
            $this->warn('Seed ngân hàng lỗi: ' . $e->getMessage());
        }
        DB::commit();
        $this->info('Seeding database thành công');
        return Command::SUCCESS;
    }
}
