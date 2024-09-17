<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\Stock\StockList;
use Illuminate\Http\Request;

class StockListController extends Controller
{
    public function index()
    {
        $indexesArray = [
            'nifty' => 'NIFTY',
            'nifty-bank' => 'BANKNIFTY',
            'sp-bse-sensex' => 'SENSEX',
            'nifty-midcap-select' => 'MIDCPNIFTY',
            'nifty-financial-services' => 'FINNIFTY',
            'sp-bse-bankex' => 'BANKEX',
            'india-vix' => 'INDIAVIX'
        ];

        $dataToInsert = [];

        foreach ($indexesArray as $name => $value) {
            $dataToInsert[] = [
                'name' => $name,
                'lastDate' => now(),
                'is_processed' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        try {
            StockList::insert($dataToInsert);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to insert data: '], 500);
        }

        return response()->json(['message' => 'Data successfully inserted'], 200);
    }

}
