<?php

namespace App\Jobs;

use App\Models\Ozon_posting_fbs;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class getPostingFbsOzon implements ShouldQueue
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

        Ozon_posting_fbs::query()->truncate();

        $Client = new Client([
            'base_uri' => $this->base_uri,
            'timeout' => $this->timeout,
            'verify' => $this->verify,
            'headers' => $this->headers
        ]);

        $ozonFbsDataDB = [
            'posting_number' => null, 
            'order_id' => null, 
            'order_number' => null, 
            'status' => null, 
            'delivery_method_id' => null, 
            'delivery_method_name' => null, 
            'warehouse_id' => null, 
            'warehouse' => null, 
            'tpl_provider_id' => null, 
            'tpl_provider' => null, 
            'tracking_number' => null, 
            'tpl_integration_type' => null, 
            'in_process_at' => null, 
            'shipment_date' => null, 
            'delivering_date' => null, 
            'cancel_reason_id' => null, 
            'cancel_reason' => null, 
            'cancellation_type' => null, 
            'cancelled_after_ship' => null, 
            'affect_cancellation_rating' => null, 
            'cancellation_initiator' => null, 
            'customer' => null, 
            'price' => null, 
            'offer_id' => null, 
            'name' => null, 
            'sku' => null, 
            'quantity' => null, 
            'mandatory_mark' => null, 
            'currency_code' => null, 
            'addressee' => null, 
            'barcodes' => null, 
            'region' => null, 
            'city' => null, 
            'delivery_type' => null, 
            'is_premium' => null, 
            'payment_type_group_name' => null, 
            'delivery_date_begin' => null, 
            'delivery_date_end' => null, 
            'is_legal' => null, 
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
            'cluster_from' => null, 
            'cluster_to' => null, 
            'is_express' => null, 
            'products_requiring_gtd' => null, 
            'products_requiring_country' => null, 
            'products_requiring_mandatory_mark' => null, 
            'products_requiring_rnpt' => null, 
            'parent_posting_number' => null, 
            'available_actions' => null, 
            'multi_box_qty' => null, 
            'is_multibox' => null
        ];

        $dataDB = [];
        $offset = 0;

        ini_set('memory_limit', '-1');

        do {
            $response = $Client->request('POST','/v3/posting/fbs/list', [
                'json' =>[
                    "dir" => "asc",
                    "filter" => [
                        "since" => '2023-01-01T00:00:00.000Z',
                        "status" => null,
                        "to" => date("Y-m-d")."T".date("H:i:s").".000Z"
                    ],
                    "limit" => 1000,
                    "offset" =>$offset,
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

        $requestDB = [];

        foreach($dataDB as $data){
            foreach ($data->result->postings as $post) {
                for ($productNum = 0; $productNum < count($post->products); $productNum++) {
                    $ozonFbsDataDB = [
                        'posting_number' => $post->posting_number, 
                        'order_id' => $post->order_id, 
                        'order_number' => $post->order_number, 
                        'status' => $post->status, 
                        'delivery_method_id' => $post->delivery_method->id, 
                        'delivery_method_name' => $post->delivery_method->name, 
                        'warehouse_id' => $post->delivery_method->warehouse_id, 
                        'warehouse' => $post->delivery_method->warehouse, 
                        'tpl_provider_id' => $post->delivery_method->tpl_provider_id,
                        'tpl_provider' => $post->delivery_method->tpl_provider, 
                        'tracking_number' => $post->tracking_number, 
                        'tpl_integration_type' => $post->tpl_integration_type, 
                        'in_process_at' => date('Y-m-d H:i:s', strtotime($post->in_process_at)), 
                        'shipment_date' => date('Y-m-d H:i:s', strtotime($post->shipment_date)), 
                        'delivering_date' => date('Y-m-d H:i:s', strtotime($post->delivering_date)), 
                        'cancel_reason_id' => $post->cancellation->cancel_reason_id, 
                        'cancel_reason' => $post->cancellation->cancel_reason, 
                        'cancellation_type' => $post->cancellation->cancellation_type, 
                        'cancelled_after_ship' => $post->cancellation->cancelled_after_ship, 
                        'affect_cancellation_rating' => $post->cancellation->affect_cancellation_rating, 
                        'cancellation_initiator' => $post->cancellation->cancellation_initiator, 
                        'customer' => $post->customer, 
                        'price' => $post->products[$productNum]->price, 
                        'offer_id' => $post->products[$productNum]->offer_id, 
                        'name' => $post->products[$productNum]->name, 
                        'sku' => $post->products[$productNum]->sku, 
                        'quantity' => $post->products[$productNum]->quantity, 
                        'mandatory_mark' => json_encode($post->products[$productNum]->mandatory_mark), 
                        'currency_code' => $post->products[$productNum]->currency_code, 
                        'addressee' => $post->addressee, 
                        'barcodes' => $post->barcodes, 
                        'region' => $post->analytics_data->region, 
                        'city' => $post->analytics_data->city, 
                        'delivery_type' => $post->analytics_data->delivery_type, 
                        'is_premium' => $post->analytics_data->is_premium, 
                        'payment_type_group_name' => $post->analytics_data->payment_type_group_name, 
                        'delivery_date_begin' => date('Y-m-d H:i:s', strtotime($post->analytics_data->delivery_date_begin)), 
                        'delivery_date_end' => date('Y-m-d H:i:s', strtotime($post->analytics_data->delivery_date_end)), 
                        'is_legal' => $post->analytics_data->is_legal, 
                        'commission_amount' => $post->financial_data->products[$productNum]->commission_amount, 
                        'commission_percent' => $post->financial_data->products[$productNum]->commission_percent, 
                        'payout' => $post->financial_data->products[$productNum]->payout, 
                        'product_id' => $post->financial_data->products[$productNum]->product_id, 
                        'old_price' => $post->financial_data->products[$productNum]->old_price, 
                        'total_discount_value' => $post->financial_data->products[$productNum]->total_discount_value, 
                        'total_discount_percent' => $post->financial_data->products[$productNum]->total_discount_percent, 
                        'actions' => json_encode($post->financial_data->products[$productNum]->actions), 
                        'picking' => json_encode($post->financial_data->products[$productNum]->picking), 
                        'client_price' => $post->financial_data->products[$productNum]->client_price, 
                        'marketplace_service_item_fulfillment' => $post->financial_data->posting_services->marketplace_service_item_fulfillment, 
                        'marketplace_service_item_pickup' => $post->financial_data->posting_services->marketplace_service_item_pickup, 
                        'marketplace_service_item_dropoff_pvz' => $post->financial_data->posting_services->marketplace_service_item_dropoff_pvz, 
                        'marketplace_service_item_dropoff_sc' => $post->financial_data->posting_services->marketplace_service_item_dropoff_sc,
                        'marketplace_service_item_dropoff_ff' => $post->financial_data->posting_services->marketplace_service_item_dropoff_ff, 
                        'marketplace_service_item_direct_flow_trans' => $post->financial_data->posting_services->marketplace_service_item_direct_flow_trans,
                        'marketplace_service_item_return_flow_trans' => $post->financial_data->posting_services->marketplace_service_item_return_flow_trans, 
                        'marketplace_service_item_deliv_to_customer' => $post->financial_data->posting_services->marketplace_service_item_deliv_to_customer, 
                        'marketplace_service_item_return_not_deliv_to_customer' => $post->financial_data->posting_services->marketplace_service_item_return_not_deliv_to_customer, 
                        'marketplace_service_item_return_part_goods_customer' => $post->financial_data->posting_services->marketplace_service_item_return_part_goods_customer, 
                        'marketplace_service_item_return_after_deliv_to_customer' => $post->financial_data->posting_services->marketplace_service_item_return_after_deliv_to_customer, 
                        'cluster_from' => $post->financial_data->cluster_from, 
                        'cluster_to' => $post->financial_data->cluster_to,
                        'is_express' => $post->is_express, 
                        'products_requiring_gtd' => json_encode($post->requirements->products_requiring_gtd), 
                        'products_requiring_country' => json_encode($post->requirements->products_requiring_country), 
                        'products_requiring_mandatory_mark' => json_encode($post->requirements->products_requiring_mandatory_mark), 
                        'products_requiring_rnpt' => json_encode($post->requirements->products_requiring_rnpt), 
                        'parent_posting_number' => $post->parent_posting_number, 
                        'available_actions' => json_encode($post->available_actions), 
                        'multi_box_qty' => $post->multi_box_qty, 
                        'is_multibox' => $post->is_multibox
                    ];

                    $requestDB[] = $ozonFbsDataDB;
                }
            }
        }

        foreach(array_chunk($requestDB, 500) as $request){
            Ozon_posting_fbs::insert($request);
        }
    }
}
