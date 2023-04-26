<?php

namespace App\Jobs;

use App\Models\Wb_stocks;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class getStocks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $base_uri;
    protected $timeout;
    protected $verify;
    protected $headers;

    public $tries = 5;

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

        Wb_stocks::query()->truncate();

        $Client = new Client([
            'base_uri' => $this->base_uri,
            'timeout' => $this->timeout,
            'verify' => $this->verify,
            'headers' => $this->headers
        ]);

        $stocksDataDB = [
            'date' => null, 
            'last_change_date' => null, 
            'supplier_article' => null, 
            'tech_size' => null, 
            'barcode' => null, 
            'quantity' => null, 
            'is_supply' => null, 
            'is_realization' => null, 
            'quantity_full' => null, 
            'warehouse_name' => null, 
            'nm_id' => null, 
            'subject' => null, 
            'category' => null, 
            'days_on_site' => null, 
            'brand' => null, 
            'sc_code' => null, 
            'price' => null, 
            'discount' => null,
        ];

        $requestDB = [];

        $response = $Client->request('GET','supplier/stocks', [
            'query' => [
                'dateFrom' => '2023-01-01',
                ]
        ]);

        $data = json_decode($response->getBody()->getContents());

        

        foreach ($data as $stock) {
            $stocksDataDB = [
                'date' => date("Y-m-d"), 
                'last_change_date' => $stock->lastChangeDate, 
                'supplier_article' => $stock->supplierArticle, 
                'tech_size' => $stock->techSize, 
                'barcode' => $stock->barcode, 
                'quantity' => $stock->quantity, 
                'is_supply' => $stock->isSupply, 
                'is_realization' => $stock->isRealization, 
                'quantity_full' => $stock->quantityFull, 
                'warehouse_name' => $stock->warehouseName, 
                'nm_id' => $stock->nmId, 
                'subject' => $stock->subject, 
                'category' => $stock->category, 
                'days_on_site' => $stock->daysOnSite, 
                'brand' => $stock->brand, 
                'sc_code' => $stock->SCCode, 
                'price' => $stock->Price, 
                'discount' => $stock->Discount
            ];

            $requestDB[] = $stocksDataDB;    
        }

        foreach(array_chunk($requestDB, 200) as $request){
            Wb_stocks::insert($request);
        }
    }
}
