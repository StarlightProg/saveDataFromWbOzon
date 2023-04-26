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
});
