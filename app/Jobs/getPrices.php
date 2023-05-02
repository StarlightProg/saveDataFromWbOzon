<?php

namespace App\Jobs;

use App\Models\Wb_incomes;
use App\Models\Wb_prices;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class getPrices implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
    public function handle(Client $Client): void
    {
        set_time_limit(0);

        $Client = new Client([
            'base_uri' => $this->base_uri,
            'timeout' => $this->timeout,
            'verify' => $this->verify,
            'headers' => $this->headers
        ]);

        $priceDataDB = [
            'date' => null,
            'nm_id' => null,
            'price' => null,
            'discount' => null,
            'promo_code' => null
        ];

        $requestDB = [];

        $response = $Client->request('GET','v1/info');

        $data = json_decode($response->getBody()->getContents());

        foreach ($data as $price) {  
            $priceDataDB=[
                'date' => date("Y-m-d"),
                'nm_id' => $price->nmId,
                'price' => $price->price,
                'discount' => $price->discount,
                'promo_code' => $price->promoCode,
            ];

            $requestDB[] = $priceDataDB;   
        }  

        foreach(array_chunk($requestDB, 200) as $request){
            Wb_prices::insert($request);
        }
    }
}
