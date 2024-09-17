<?php

namespace App\Http\Controllers\Stock;

use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use App\Models\Stock\OptionsBackup;
use App\Models\Stock\StockList;
use Carbon\Carbon;

class OptionsBackupController extends Controller
{
    protected $indexesArray = [
        'nifty' => 'NIFTY',
        'nifty-bank' => 'BANKNIFTY',
        'sp-bse-sensex' => 'SENSEX',
        'nifty-midcap-select' => 'MIDCPNIFTY',
        'nifty-financial-services' => 'FINNIFTY',
        'sp-bse-bankex' => 'BANKEX',
        'india-vix' => 'INDIAVIX'
    ];

    public function index()
    {
        $sl = StockList::where('is_processed', false)->first();

        if (!$sl) {
            return response()->json(['error' => 'No more data to process.'], 500);
        }

        $response = $this->fetchStockData($sl->name);

        if (!$response->successful()) {
            return response()->json(['error' => 'Failed to fetch data'], 500);
        }

        $dataToInsert = $this->prepareDataToInsert($response->json(), $sl);

        try {
            OptionsBackup::insert($dataToInsert);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to insert data'], 500);
        }

        $sl->is_processed = true;
        $sl->save();

        return response()->json(['message' => 'Data successfully inserted'], 200);
    }

    protected function fetchStockData($name)
    {
        if ($name === 'india-vix') {
            $url = 'https://groww.in/v1/api/charting_service/v2/chart/exchange/NSE/segment/CASH/INDIAVIX/daily?intervalInMinutes=1&minimal=true';
            return Http::get($url);
        }

        $url = 'https://groww.in/v1/api/option_chain_service/v1/option_chain/derivatives/' . $name;
        return Http::get($url);
    }

    protected function prepareDataToInsert($jsonData, $sl)
    {
        $dataToInsert = [];

        if ($sl->name === 'india-vix') {
            $dataToInsert[] = [
                'indexName' => $this->indexesArray[$sl->name],
                'strike_price' => date('Ymd'),
                'currentExpiry' => date('Y-m-d'),
                'callOption' => '',
                'is_processed' => 0,
                'putOption' => json_encode($jsonData['candles']),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        } else {
            $currentExpiry = $jsonData['optionChain']['expiryDetailsDto']['currentExpiry'];
            $sps = $jsonData['optionChain']['optionChains'];

            foreach ($sps as $sp) {
                $dataToInsert[] = [
                    'indexName' => $this->indexesArray[$sl->name],
                    'strike_price' => $sp['strikePrice'],
                    'currentExpiry' => $currentExpiry,
                    'callOption' => '',
                    'is_processed' => 0,
                    'putOption' => '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        return $dataToInsert;
    }

    public function getsingleStrickChart() 
    {
        $sl = OptionsBackup::where('is_processed', false)->first();

        if (!$sl) {
            return response()->json(['error' => 'No more data to process.'], 500);
        }

        $dateObject = Carbon::createFromFormat('Y-m-d', $sl->currentExpiry);
        $date = $dateObject->format('y') . $dateObject->format('n') . $dateObject->format('j');
        $modifiedPrice = substr($sl->strike_price, 0, -2);

        $exchange = 'NSE';
        if(in_array($sl->indexName, ['BANKEX', 'SENSEX']))
        {
            $exchange = 'BSE';
        }

        foreach ($this->getOptionTypes() as $type => $suffix) {
            
            $url = "https://groww.in/v1/api/stocks_fo_data/v1/charting_service/chart/exchange/{$exchange}/segment/FNO/{$sl->indexName}{$date}{$modifiedPrice}{$suffix}/daily?intervalInMinutes=1";

            // echo $url;exit;

            echo '<title>' . $sl->indexName . ': ' . $modifiedPrice.'</title>'.$sl->indexName.': '.$modifiedPrice. "<br>";

            $response = Http::get($url);

            if ($response->successful()) {
                $sl->$type = json_encode($response->json()['candles']);
            }
        }

        $sl->is_processed = true;
        $sl->save();

        header("Refresh:1"); 
    }

    protected function getOptionTypes()
    {
        return [
            'callOption' => 'CE',
            'putOption' => 'PE'
        ];
    }
}
