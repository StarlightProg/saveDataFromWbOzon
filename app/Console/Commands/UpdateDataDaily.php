<?php

namespace App\Console\Commands;

use App\Jobs\getSales;
use App\Jobs\getOrders;
use App\Jobs\getPrices;
use App\Jobs\getStocks;
use App\Jobs\getIncomes;
use App\Jobs\getStocksOzon;
use App\Jobs\getSalesReports;
use App\Jobs\getPostingFboOzon;
use App\Jobs\getPostingFbsOzon;
use Illuminate\Console\Command;

class UpdateDataDaily extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-data-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Обновляет данные о заказах с Wildberries и Ozon';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $headersOzon = [
            "Client-Id" => env('CLIENT_ID_OZON'),
            "Api-Key" => env('AUTH_TOKEN_OZON')
        ];

        $ozonClientData = array(
            'base_uri' => "https://api-seller.ozon.ru/",
            'timeout' => 20.0,
            'verify' => false,
            'headers' => $headersOzon
        );

        $headersStatistics= ["Content-Type" => "application/json",
            "Authorization" => env('AUTH_TOKEN_WB_STATISTICS')];

        $headersSuppliers = ["Content-Type" => "application/json",
        "Authorization" => env('AUTH_TOKEN_WB_SUPPLIERS')];

        $statisticsClientData = array(
            'base_uri' => "https://statistics-api.wildberries.ru/api/v1/",
            'timeout' => 120.0,
            'verify' => false,
            'headers' => $headersStatistics
        );

        $suppliersClientData  = array(
            'base_uri' => "https://suppliers-api.wildberries.ru/public/api/",
            'timeout' => 2.0,
            'verify' => false,
            'headers' => $headersSuppliers
        );

        ini_set('memory_limit', '-1');

        getIncomes::dispatch($statisticsClientData);
        getPrices::dispatch($suppliersClientData);
        getStocks::dispatch($statisticsClientData);
        getSales::dispatch($statisticsClientData);
        getOrders::dispatch($statisticsClientData);
        getStocksOzon::dispatch($ozonClientData);
        getPostingFboOzon::dispatch($ozonClientData);
        getPostingFbsOzon::dispatch($ozonClientData);
        getSalesReports::dispatch($statisticsClientData);

        ini_set('memory_limit', '128M');
    }
}
