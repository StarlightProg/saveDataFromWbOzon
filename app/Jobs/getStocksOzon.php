<?php

namespace App\Jobs;

use App\Models\Ozon_stocks;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class getStocksOzon implements ShouldQueue
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

        Ozon_stocks::query()->truncate();


        $Client = new Client([
            'base_uri' => $this->base_uri,
            'timeout' => $this->timeout,
            'verify' => $this->verify,
            'headers' => $this->headers
        ]);

        $ordersDataDB = [
            'date' => null, 
            'product_id' => null, 
            'offer_id' => null, 
            'fbo_present' => null,
            'fbo_reserved' => null,
            'fbs_present' => null,
            'fbs_reserved' => null
        ];

        $dataDB = [];
        $offset = 0;

        

        do {
            $response = $Client->request('POST','v3/product/info/stocks', [
                'json' =>[
                    "filter" => [
                        "offer_id" => null,
                        "product_id" => null,
                        "visibility" => "ALL"
                    ],
                    "last_id" => "",
                    "offset" =>$offset,
                    "limit" => 1000
                ]
            ]);
    
            $data = json_decode($response->getBody()->getContents());

            $dataDB[] = $data;
            $offset+=1000;
        } while (!empty($data->result->postings));
        
        $requestDB = [];

        foreach($dataDB as $data){
            foreach ($data->result->items as $stock) {
                $ordersDataDB = [
                    'date' => date("Y-m-d"), 
                    'product_id' => $stock->product_id, 
                    'offer_id' => $stock->offer_id, 
                    'fbo_present' => $stock->stocks[0]->present,
                    'fbo_reserved' => $stock->stocks[0]->reserved,
                    'fbs_present' => $stock->stocks[1]->present,
                    'fbs_reserved' => $stock->stocks[1]->reserved
                ];

                $requestDB[] = $ordersDataDB;
            }
        }
        

        foreach(array_chunk($requestDB, 100) as $request){
            Ozon_stocks::insert($request);
        }
    }
}
