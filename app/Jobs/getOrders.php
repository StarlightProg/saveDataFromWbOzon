<?php

namespace App\Jobs;

use App\Models\Wb_orders;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class getOrders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $base_uri;
    protected $timeout;
    protected $verify;
    protected $headers;

    public $tries = 5;
    /**
     * Create a new job instance.
     */
    public function __construct($ClientData)
    {
        $this->base_uri = $ClientData['base_uri'];
        $this->timeout = $ClientData['timeout'];
        $this->verify = $ClientData['verify'];
        $this->headers = $ClientData['headers'];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        set_time_limit(0);

        $Client = new Client([
            'base_uri' => $this->base_uri,
            'timeout' => $this->timeout,
            'verify' => $this->verify,
            'headers' => $this->headers
        ]);

        $ordersDataDB = [
            'g_number' => null, 
            'date' => null, 
            'last_change_date' => null, 
            'supplier_article' => null, 
            'tech_size' => null, 
            'barcode' => null, 
            'total_price' => null, 
            'discount_percent' => null, 
            'warehouse_name' => null, 
            'oblast' => null, 
            'income_id' => null, 
            'odid' => null, 
            'nm_id' => null, 
            'subject' => null, 
            'category' => null,
            'brand' => null, 
            'is_cancel' => null, 
            'cancel_dt' => null, 
            'sticker' => null, 
            'srid' => null
        ];

        ini_set('memory_limit', '-1');

        $response = $Client->request('GET','supplier/orders', [
            'query' => ['dateFrom' => '2023-01-01']
        ]);

        $data = json_decode($response->getBody()->getContents());

        foreach ($data as $order) {
            $ordersDataDB = [
                'g_number' => $order->gNumber, 
                'date' => $order->date, 
                'last_change_date' => $order->lastChangeDate, 
                'supplier_article' => $order->supplierArticle, 
                'tech_size' => $order->techSize, 
                'barcode' => $order->barcode, 
                'total_price' => $order->totalPrice, 
                'discount_percent' => $order->discountPercent, 
                'warehouse_name' => $order->warehouseName, 
                'oblast' => $order->oblast, 
                'income_id' => $order->incomeID, 
                'odid' => $order->odid, 
                'nm_id' => $order->nmId, 
                'subject' => $order->subject, 
                'category' => $order->category,
                'brand' => $order->brand, 
                'is_cancel' => $order->isCancel, 
                'cancel_dt' => $order->cancel_dt, 
                'sticker' => $order->sticker, 
                'srid' => $order->srid
            ];

            $requestDB[] = $ordersDataDB;
        }

        foreach(array_chunk($requestDB, 2000) as $request){
            Wb_orders::upsert($request,['odid']);
        }
        //ini_set('memory_limit', '128M');

        //dd(json_decode($response->getBody()->getContents()));
        //dd($dataIncomes);
    }
}
