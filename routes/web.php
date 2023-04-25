<?php

use App\Jobs\getIncomes;
use App\Jobs\getOrders;
use App\Jobs\getPostingFboOzon;
use App\Jobs\getPostingFbsOzon;
use App\Jobs\getPrices;
use App\Jobs\getSales;
use App\Jobs\getSalesReports;
use App\Jobs\getStocks;
use App\Jobs\getStocksOzon;
use App\Models\TestTable;
use App\Models\Wb_incomes;
use App\Models\Wb_orders;
use App\Models\Wb_prices;
use App\Models\Wb_sales;
use GuzzleHttp\Client;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
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
        //Wb_prices::query()->truncate();
 
        dd(Wb_prices::all());
    //getPrices::dispatch($suppliersClientData);
    echo("nwqdjqwdjnqw");
});
