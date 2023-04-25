<?php

namespace App\Jobs;

use App\Models\Wb_sales;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class getSales implements ShouldQueue
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
    public function handle(Client $Client): void
    {
        $Client = new Client([
            'base_uri' => $this->base_uri,
            'timeout' => $this->timeout,
            'verify' => $this->verify,
            'headers' => $this->headers
        ]);

        $saleDataDB = [
            'g_number' => null, 
            'date' => null, 
            'last_change_date' => null, 
            'supplier_article' => null, 
            'tech_size' => null, 
            'barcode' => null, 
            'total_price' => null, 
            'discount_percent' => null, 
            'is_supply' => null, 
            'is_realization' => null, 
            'promo_code_discount' => null, 
            'warehouse_name' => null, 
            'country_name' => null, 
            'oblast_okrug_name' => null, 
            'region_name' => null, 
            'income_id' => null, 
            'sale_id' => null, 
            'sale_id_status' => null, 
            'odid' => null, 
            'spp' => null, 
            'for_pay' => null, 
            'finished_price' => null, 
            'price_with_disc' => null, 
            'nm_id' => null, 
            'subject' => null, 
            'category' => null, 
            'brand' => null, 
            'is_storno' => null, 
            'sticker' => null, 
            'srid' => null,
        ];

        $requestDB = [];

        $responseSales = $Client->request('GET','supplier/sales', [
            'query' => ['dateFrom' => '2023-01-01']
        ]);

        ini_set('memory_limit', '-1');

        $dataSales = json_decode($responseSales->getBody()->getContents());

        

        foreach($dataSales as $sale){

            switch ($sale->saleID[0]) {
                case 'S':
                    $sale_id_status = 'sale';
                    break;
                case 'R':
                    $sale_id_status = 'return';
                    break;
                case 'D':
                    $sale_id_status = 'extraPay';
                    break;
                case 'A':
                    $sale_id_status = 'stornoSale';
                    break;
                case 'B':
                    $sale_id_status = 'stornoReturn';
                    break;
                default:
                    $sale_id_status = 'undefined';
                    break;
            }
            

            $saleDataDB = [
                'g_number' => $sale->gNumber,
                'date' => $sale->date,
                'last_change_date' => $sale->lastChangeDate,
                'supplier_article' => $sale->supplierArticle, 
                'tech_size' => $sale->techSize, 
                'barcode' => $sale->barcode, 
                'total_price' => $sale->totalPrice, 
                'discount_percent' => $sale->discountPercent, 
                'is_supply' => $sale->isSupply, 
                'is_realization' => $sale->isRealization, 
                'promo_code_discount' => $sale->promoCodeDiscount, 
                'warehouse_name' => $sale->warehouseName, 
                'country_name' => $sale->countryName, 
                'oblast_okrug_name' => $sale->oblastOkrugName, 
                'region_name' => $sale->regionName, 
                'income_id' => $sale->incomeID, 
                'sale_id' => $sale->saleID, 
                'sale_id_status' => $sale_id_status, 
                'odid' => $sale->odid, 
                'spp' => $sale->spp, 
                'for_pay' => $sale->forPay, 
                'finished_price' => $sale->finishedPrice, 
                'price_with_disc' => $sale->priceWithDisc, 
                'nm_id' => $sale->nmId, 
                'subject' => $sale->subject, 
                'category' => $sale->category, 
                'brand' => $sale->brand, 
                'is_storno' => $sale->IsStorno, 
                'sticker' => $sale->sticker, 
                'srid' => $sale->srid,
            ];

            $requestDB[] = $saleDataDB;
        }

        foreach(array_chunk($requestDB, 200) as $request){
            Wb_sales::upsert($request,['sale_id']);
        }
       
    }
}
