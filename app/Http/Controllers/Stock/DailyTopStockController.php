<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\Stock\DailyTopStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DailyTopStockController extends Controller
{
    public function index() {

        $indexes = [
            'GIDXNIFTY100',
            'GIDXNIFTY500',
            'GIDXNIFMDCP100',
            'GIDXNIFSMCP100'
        ];

        $types = [
            'TOP_GAINERS',
            'TOP_LOSERS'
        ];
      
        
        foreach($indexes as $index) {
            $d = [];
 

            foreach($types as $type) {
                $url = "https://groww.in/v1/api/stocks_data/explore/v2/indices/{$index}/market_trends?discovery_filter_types={$type}&size=5";

                $response = Http::get($url);

                if (!$response->successful()) {
                    return response()->json(['error' => 'Failed to fetch data'], 500);
                }


                $typex = ($type == 'TOP_GAINERS') ? 'gainers' : 'loosers';

                foreach($response->json()['categoryResponseMap'][$type]['items'] as $item) { 
                    $d[$typex][] = [$item['company']['companyName'] => $item['stats']['dayChangePerc']];
                }

                $res[$index] = $d;

                
            }  
 

            
        }

        foreach($res as $k=>$v) { 
            $dataToInsert[] = [
                'indexName' => $k,
                'gainers' => json_encode($v['gainers']),
                'loosers' => json_encode($v['loosers']),
                'created_at' => now(),
                'updated_at' => now(),
            ]; 
        } 
        

        try {
            DailyTopStock::insert($dataToInsert);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to insert data'], 500);
        }
    }

}