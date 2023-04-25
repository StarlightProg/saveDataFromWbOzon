<?php

namespace App\Jobs;

use App\Models\Wb_sales_reports;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class getSalesReports implements ShouldQueue
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
        $this->timeout = 500;
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

        $salesReportsDataDB = [
            'realizationreport_id' => null, 
            'date_from' => null, 
            'date_to' => null, 
            'create_dt' => null, 
            'suppliercontract_code' => null, 
            'rrd_id' => null, 
            'gi_id' => null, 
            'subject_name' => null, 
            'nm_id' => null, 
            'brand_name' => null, 
            'sa_name' => null, 
            'ts_name' => null, 
            'barcode' => null, 
            'doc_type_name' => null, 
            'quantity' => null, 
            'retail_price' => null, 
            'retail_amount' => null, 
            'sale_percent' => null, 
            'commission_percent' => null, 
            'office_name' => null, 
            'supplier_oper_name' => null, 
            'order_dt' => null, 
            'sale_dt' => null, 
            'rr_dt' => null, 
            'shk_id' => null, 
            'retail_price_withdisc_rub' => null, 
            'delivery_amount' => null, 
            'return_amount' => null, 
            'delivery_rub' => null, 
            'gi_box_type_name' => null, 
            'product_discount_for_report' => null, 
            'supplier_promo' => null, 
            'rid' => null, 
            'ppvz_spp_prc' => null, 
            'ppvz_kvw_prc_base' => null, 
            'ppvz_kvw_prc' => null, 
            'ppvz_sales_commission' => null, 
            'ppvz_for_pay' => null, 
            'ppvz_reward' => null, 
            'acquiring_fee' => null, 
            'acquiring_bank' => null, 
            'ppvz_vw' => null, 
            'ppvz_vw_nds' => null, 
            'ppvz_office_id' => null, 
            'ppvz_office_name' => null, 
            'ppvz_supplier_id' => null, 
            'ppvz_supplier_name' => null, 
            'ppvz_inn' => null, 
            'declaration_number' => null, 
            'bonus_type_name' => null, 
            'sticker_id' => null, 
            'site_country' => null, 
            'penalty' => null, 
            'additional_payment' => null, 
            'srid' => null
        ];

        $requestDB = [];
        $dataDB = [];

        ini_set('memory_limit', '-1');

        $response = $Client->request('GET','supplier/reportDetailByPeriod', [
            'query' => [
                'dateFrom' => '2023-01-01',
                'dateTo' => date("Y-m-d H:i:s"),
            ],
        ]);

        $data = json_decode($response->getBody()->getContents());

        $dataDB[] = $data;

        foreach($data as $report){
            $salesReportsDataDB = [
                'realizationreport_id' => $report->realizationreport_id, 
                'date_from' => substr($report->date_from, 0, strpos($report->date_from,"T")), 
                'date_to' => substr($report->date_to, 0, strpos($report->date_from,"T")), 
                'create_dt' => substr($report->create_dt, 0, strpos($report->date_from,"Z")), 
                'suppliercontract_code' => $report->suppliercontract_code, 
                'rrd_id' => $report->rrd_id, 
                'gi_id' => $report->gi_id, 
                'subject_name' => $report->subject_name, 
                'nm_id' => $report->nm_id, 
                'brand_name' => $report->brand_name, 
                'sa_name' => $report->sa_name, 
                'ts_name' => $report->ts_name, 
                'barcode' => $report->barcode, 
                'doc_type_name' => $report->doc_type_name, 
                'quantity' => $report->quantity, 
                'retail_price' => $report->retail_price, 
                'retail_amount' => $report->retail_amount, 
                'sale_percent' => $report->sale_percent, 
                'commission_percent' => $report->commission_percent, 
                'office_name' => $report->office_name, 
                'supplier_oper_name' => $report->supplier_oper_name, 
                'order_dt' => substr($report->order_dt, 0, strpos($report->date_from,"T")), 
                'sale_dt' => substr($report->sale_dt, 0, strpos($report->date_from,"T")), 
                'rr_dt' => substr($report->rr_dt, 0, strpos($report->date_from,"T")), 
                'shk_id' => $report->shk_id, 
                'retail_price_withdisc_rub' => $report->retail_price_withdisc_rub, 
                'delivery_amount' => $report->delivery_amount, 
                'return_amount' => $report->return_amount, 
                'delivery_rub' => $report->delivery_rub, 
                'gi_box_type_name' => $report->gi_box_type_name, 
                'product_discount_for_report' => $report->product_discount_for_report, 
                'supplier_promo' => $report->supplier_promo, 
                'rid' => $report->rid >= 0 ? $report->rid : 0, 
                'ppvz_spp_prc' => $report->ppvz_spp_prc, 
                'ppvz_kvw_prc_base' => $report->ppvz_kvw_prc_base, 
                'ppvz_kvw_prc' => $report->ppvz_kvw_prc, 
                'ppvz_sales_commission' => $report->ppvz_sales_commission, 
                'ppvz_for_pay' => $report->ppvz_for_pay, 
                'ppvz_reward' => $report->ppvz_reward, 
                'acquiring_fee' => $report->acquiring_fee, 
                'acquiring_bank' => $report->acquiring_bank, 
                'ppvz_vw' => $report->ppvz_vw, 
                'ppvz_vw_nds' => $report->ppvz_vw_nds, 
                'ppvz_office_id' => $report->ppvz_office_id, 
                'ppvz_office_name' => property_exists($report,'ppvz_office_name') ? $report->ppvz_office_name : null, 
                'ppvz_supplier_id' => $report->ppvz_supplier_id, 
                'ppvz_supplier_name' => property_exists($report,'ppvz_supplier_name') ? $report->ppvz_supplier_name : null, 
                'ppvz_inn' => property_exists($report,'ppvz_inn') ? $report->ppvz_inn : null, 
                'declaration_number' => $report->declaration_number, 
                'bonus_type_name' => property_exists($report,'bonus_type_name') ? $report->bonus_type_name : null, 
                'sticker_id' => property_exists($report,'sticker_id') ? $report->sticker_id : null, 
                'site_country' => property_exists($report,'site_country') ? $report->site_country : null, 
                'penalty' => property_exists($report,'penalty') ? $report->penalty : null, 
                'additional_payment' => property_exists($report,'additional_payment') ? $report->additional_payment : null, 
                'srid' => property_exists($report,'srid') ? $report->srid : null
            ];

            $requestDB[] = $salesReportsDataDB;    
        }

        $chuncks = array_chunk($requestDB, 200);

        foreach($chuncks as $request){
            Wb_sales_reports::upsert($request,['rrd_id']);
        }
    }
}
