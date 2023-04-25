<?php

namespace App\Jobs;

use App\Models\Ozon_posting_fbo;
use DateTime;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class getPostingFboOzon implements ShouldQueue
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

        Ozon_posting_fbo::query()->truncate();

        $Client = new Client([
            'base_uri' => $this->base_uri,
            'timeout' => $this->timeout,
            'verify' => $this->verify,
            'headers' => $this->headers
        ]);

        $ozonFboDataDB = [
            'order_id' => null, 
            'order_number' => null, 
            'posting_number' => null, 
            'status' => null, 
            'cancel_reason_id' => null, 
            'posting_created_at' => null, 
            'in_process_at' => null, 
            'additional_data' => null, 
            'sku' => null, 
            'name' => null, 
            'quantity' => null, 
            'offer_id' => null, 
            'price' => null, 
            'digital_codes' => null, 
            'region' => null, 
            'city' => null, 
            'delivery_type' => null, 
            'is_premium' => null, 
            'payment_type_group_name' => null, 
            'warehouse_id' => null, 
            'warehouse_name' => null, 
            'is_legal' => null, 
            'marketplace_service_item_fulfillment' => null, 
            'marketplace_service_item_pickup' => null, 
            'marketplace_service_item_dropoff_pvz' => null, 
            'marketplace_service_item_dropoff_sc' => null, 
            'marketplace_service_item_dropoff_ff' => null, 
            'marketplace_service_item_direct_flow_trans' => null, 
            'marketplace_service_item_return_flow_trans' => null, 
            'marketplace_service_item_deliv_to_customer' => null, 
            'marketplace_service_item_return_not_deliv_to_customer' => null, 
            'marketplace_service_item_return_part_goods_customer' => null, 
            'marketplace_service_item_return_after_deliv_to_customer' => null, 
            'currency_code' => null, 
            'commission_amount' => null, 
            'commission_percent' => null, 
            'payout' => null, 
            'product_id' => null, 
            'old_price' => null, 
            'total_discount_value' => null, 
            'total_discount_percent' => null, 
            'actions' => null, 
            'picking' => null, 
            'client_price' => null, 
            'cluster_from' => null, 
            'cluster_to' => null,
        ];

        $dataDB = [];
        $offset = 0;

        do{
          $response = $Client->request('POST','/v2/posting/fbo/list', [
              'json' =>[
                  "dir" => "asc",
                  "filter" => [
                      "since" => '2023-01-01T00:00:00.000Z',
                      "status" => null,
                      "to" => date("Y-m-d")."T".date("H:i:s").".000Z"
                  ],
                  "limit" => 1000,
                  "with" => [
                      "analytics_data" => true,
                      "financial_data" => true
                  ]
              ]
          ]);
          $data = json_decode($response->getBody()->getContents());

          $dataDB[] = $data;
          $offset+=1000;
        } while (!empty($data->result->postings));
    
        $data = json_decode($response->getBody()->getContents());
        
        $requestDB = [];

        foreach($dataDB as $data){
          foreach ($data->result as $post) {

              $ozonFboDataDB = [
                  'order_id' => $post->order_id, 
                  'order_number' => $post->order_number, 
                  'posting_number' => $post->posting_number, 
                  'status' => $post->status, 
                  'cancel_reason_id' => $post->cancel_reason_id, 
                  'posting_created_at' => date('Y-m-d H:i:s', strtotime($post->created_at)), 
                  'in_process_at' => date('Y-m-d H:i:s', strtotime($post->in_process_at)), 
                  'additional_data' => json_encode($post->additional_data), 
                  'sku' => $post->products[0]->sku, 
                  'name' => $post->products[0]->name, 
                  'quantity' => $post->products[0]->quantity, 
                  'offer_id' => $post->products[0]->offer_id, 
                  'price' => $post->products[0]->price, 
                  'digital_codes' => json_encode($post->products[0]->digital_codes), 
                  'region' => $post->analytics_data->region, 
                  'city' => $post->analytics_data->city, 
                  'delivery_type' => $post->analytics_data->delivery_type, 
                  'is_premium' => $post->analytics_data->is_premium, 
                  'payment_type_group_name' => $post->analytics_data->payment_type_group_name, 
                  'warehouse_id' => $post->analytics_data->warehouse_id, 
                  'warehouse_name' => $post->analytics_data->warehouse_name, 
                  'is_legal' => $post->analytics_data->is_legal, 
                  'marketplace_service_item_fulfillment' => $post->financial_data->products[0]->item_services->marketplace_service_item_fulfillment, 
                  'marketplace_service_item_pickup' => $post->financial_data->products[0]->item_services->marketplace_service_item_pickup, 
                  'marketplace_service_item_dropoff_pvz' => $post->financial_data->products[0]->item_services->marketplace_service_item_dropoff_pvz, 
                  'marketplace_service_item_dropoff_sc' => $post->financial_data->products[0]->item_services->marketplace_service_item_dropoff_sc, 
                  'marketplace_service_item_dropoff_ff' => $post->financial_data->products[0]->item_services->marketplace_service_item_dropoff_ff, 
                  'marketplace_service_item_direct_flow_trans' => $post->financial_data->products[0]->item_services->marketplace_service_item_direct_flow_trans, 
                  'marketplace_service_item_return_flow_trans' => $post->financial_data->products[0]->item_services->marketplace_service_item_return_flow_trans, 
                  'marketplace_service_item_deliv_to_customer' => $post->financial_data->products[0]->item_services->marketplace_service_item_deliv_to_customer, 
                  'marketplace_service_item_return_not_deliv_to_customer' => $post->financial_data->products[0]->item_services->marketplace_service_item_return_not_deliv_to_customer, 
                  'marketplace_service_item_return_part_goods_customer' => $post->financial_data->products[0]->item_services->marketplace_service_item_return_part_goods_customer, 
                  'marketplace_service_item_return_after_deliv_to_customer' => $post->financial_data->products[0]->item_services->marketplace_service_item_return_after_deliv_to_customer, 
                  'currency_code' => $post->products[0]->currency_code, 
                  'commission_amount' => $post->financial_data->products[0]->commission_amount, 
                  'commission_percent' => $post->financial_data->products[0]->commission_percent, 
                  'payout' => $post->financial_data->products[0]->payout, 
                  'product_id' => $post->financial_data->products[0]->product_id, 
                  'old_price' => $post->financial_data->products[0]->old_price, 
                  'total_discount_value' => $post->financial_data->products[0]->total_discount_value, 
                  'total_discount_percent' => $post->financial_data->products[0]->total_discount_percent, 
                  'actions' => json_encode($post->financial_data->products[0]->actions), 
                  'picking' => json_encode($post->financial_data->products[0]->picking), 
                  'client_price' => $post->financial_data->products[0]->client_price, 
                  'cluster_from' => null, 
                  'cluster_to' => null,
              ];

              $requestDB[] = $ozonFboDataDB;
          }
        }

        foreach(array_chunk($requestDB, 100) as $request){
            Ozon_posting_fbo::insert($request);
        }
    }
}
