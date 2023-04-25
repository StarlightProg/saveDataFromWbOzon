<?php

namespace App\Jobs;

use App\Models\Wb_incomes;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class getIncomes implements ShouldQueue
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

        Wb_incomes::query()->truncate();

        $Client = new Client([
            'base_uri' => $this->base_uri,
            'timeout' => $this->timeout,
            'verify' => $this->verify,
            'headers' => $this->headers
        ]);

        $incomesDataDB = [
            'income_id' => null, 
            'number' => null, 
            'date' => null, 
            'last_change_date' => null, 
            'supplier_article' => null, 
            'tech_size' => null, 
            'barcode' => null, 
            'quantity' => null, 
            'total_price' => null, 
            'date_close' => null, 
            'warehouse_name' => null, 
            'nm_id' => null, 
            'status' => null
        ];

        $requestDB = [];

        $response = $Client->request('GET','supplier/incomes', [
            'query' => ['dateFrom' => '2023-01-01']
        ]);

        $dataIncomes = json_decode($response->getBody()->getContents());
        

        foreach ($dataIncomes as $income) {
            $incomesDataDB = [
                'income_id' => $income->incomeId, 
                'number' => $income->number, 
                'date' => $income->date, 
                'last_change_date' => $income->lastChangeDate, 
                'supplier_article' => $income->supplierArticle, 
                'tech_size' => $income->techSize, 
                'barcode' => $income->barcode, 
                'quantity' => $income->quantity, 
                'total_price' => $income->totalPrice, 
                'date_close' => $income->dateClose, 
                'warehouse_name' => $income->warehouseName, 
                'nm_id' => $income->nmId, 
                'status' => $income->status
            ];

            $requestDB[] = $incomesDataDB;
        }

        foreach(array_chunk($requestDB, 200) as $request){
            Wb_incomes::insert($request);
        }

           
    }
}
