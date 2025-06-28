<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Models\Rate;
use Illuminate\Support\Facades\Log;
use Validator;

class ApiController extends Controller
{
    public function btcCalculation(Request $request)
    {
        try
        {


            $validator = Validator::make($request->all(), [
                'device_token' => 'required|string',
            ]);

            if($validator->fails()){
                return response()->json(['status'=>false, 'message'=>'The given data was invalid', 'data'=>$validator->errors()],422);
            }

            $btcAmount = $request->has('amount')?$request->amount:1;
            $url = "https://api.binance.com/api/v3/ticker/price?symbol=BTCUSDT";

            // Initialize cURL
            $ch = curl_init();

            // Set cURL options
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // Execute the request
            $response = curl_exec($ch);

            // Close cURL
            curl_close($ch);

            // Decode the JSON response
            $data = json_decode($response, true);

            // Get the BTC to USD rate
            $rate = $data['price'] ?? 0;
            //return $rate;
            if($rate == 0)
            {
                return response()->json(['status'=>false, 'rate'=>0, 'data'=>array()],500);
            }
            //return $data;
            // Calculate total USD value
            $totalUsd = $btcAmount * $rate;
           // return $totalUsd;
            $odata = array();
            $rates = Rate::where('alerm_sent',NULL)->where('device_token', $request->device_token)
                        ->where(function ($query) use ($totalUsd) {
                            $query->where(function ($q) use ($totalUsd) {
                                $q->where('type', 'Up')
                                  ->where('rate', '<=', $totalUsd);
                            })->orWhere(function ($q) use ($totalUsd) {
                                $q->where('type', 'Down')
                                  ->where('rate', '>=', $totalUsd);
                            });
                        })

                    ->get();

            $nearest = null;
            $smallestDiff = PHP_INT_MAX;
            $getType = null;

            foreach ($rates as $row) {
                $rate = floatval($row->rate);
                $diff = abs($totalUsd - $rate);

                if ($diff < $smallestDiff) {
                    $smallestDiff = $diff;
                    $nearest = $row;
                    $getType = $row->type;
                }
            }

            return response()->json([
                'status' => true,
                'rate'=>$totalUsd,
                'message' => "The BTC price is {$getType}, The current BTC price is {$totalUsd}",
                'data' => $nearest,
            ]);



            //return response()->json(['status'=>count($rates)>0, 'rate'=>$totalUsd, 'data'=>$rates]);

        } catch(Exception $e){
    		return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
    	}
    }

    public function btcCalculationBK(Request $request)
    {
        try
        {


            $validator = Validator::make($request->all(), [
                'device_token' => 'required|string',
            ]);

            if($validator->fails()){
                return response()->json(['status'=>false, 'message'=>'The given data was invalid', 'data'=>$validator->errors()],422);
            }

            $btcAmount = $request->has('amount')?$request->amount:1;
            $url = "https://api.coingecko.com/api/v3/simple/price?ids=bitcoin&vs_currencies=usd";

            // Initialize cURL
            $ch = curl_init();

            // Set cURL options
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // Execute the request
            $response = curl_exec($ch);

            // Close cURL
            curl_close($ch);

            // Decode the JSON response
            $data = json_decode($response, true);

            // Get the BTC to USD rate
            $rate = $data['bitcoin']['usd'] ?? 0;
            //return $rate;
            if($rate == 0)
            {
                return response()->json(['status'=>false, 'rate'=>0, 'data'=>array()],500);
            }
            //return $data;
            // Calculate total USD value
            $totalUsd = $btcAmount * $rate;
            // return $totalUsd;
            $odata = array();
            $rates = Rate::where('alerm_sent',NULL)->where('device_token', $request->device_token)
                ->where(function ($query) use ($totalUsd) {
                    $query->where(function ($q) use ($totalUsd) {
                        $q->where('type', 'Up')
                            ->where('rate', '<=', $totalUsd);
                    })->orWhere(function ($q) use ($totalUsd) {
                        $q->where('type', 'Down')
                            ->where('rate', '>=', $totalUsd);
                    });
                })

                ->get();

            $nearest = null;
            $smallestDiff = PHP_INT_MAX;
            $getType = null;

            foreach ($rates as $row) {
                $rate = floatval($row->rate);
                $diff = abs($totalUsd - $rate);

                if ($diff < $smallestDiff) {
                    $smallestDiff = $diff;
                    $nearest = $row;
                    $getType = $row->type;
                }
            }

            return response()->json([
                'status' => true,
                'rate'=>$totalUsd,
                'message' => "The BTC price is {$getType}, The current BTC price is {$totalUsd}",
                'data' => $nearest,
            ]);



            //return response()->json(['status'=>count($rates)>0, 'rate'=>$totalUsd, 'data'=>$rates]);

        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function setAlermStatus(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'rate_id' => 'required|integer',
                'alerm_sent' => 'required|in:Yes,No'
            ]);

            if($validator->fails()){
                return response()->json(['status'=>false, 'message'=>'The given data was invalid', 'data'=>$validator->errors()],422);
            }

            $rate = Rate::findorfail($request->rate_id);
            $rate->alerm_sent = $request->alerm_sent;
            $rate->update();

            return response()->json(['status'=>true, 'rate_id'=>intval($rate->id), 'messsage'=>'Successfully Updated']);

        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function btcToUsdRate()
    {
        try
        {
            $url = "https://api.binance.com/api/v3/ticker/price?symbol=BTCUSDT";

            // Initialize cURL
            $ch = curl_init();

            // Set cURL options
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // Execute the request
            $response = curl_exec($ch);

            // Close cURL
            curl_close($ch);

            // Decode the JSON response
            $data = json_decode($response, true);

            // Get the BTC to USD rate
            $rate = $data['price'] ? (float) $data['price'] : 0;

            if($rate === 0)
            {
                return response()->json([
                    'status' => false,
                    'rate' => 0,
                    'message' => "API Service Unavailable.",
                    'data' => array()
                ],500);
            }

            return response()->json([
                'status' => true,
                'message' => "BTC USD value calculated successfully.",
                'data' => number_format($rate, 2, '.', ','),
            ]);

        } catch(Exception $e){
            // Log the error
            Log::error('Error in getting BTC to USD rate: ', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => false,
                'message' => "Something went wrong!!!",
                'data' => array()
            ],500);
        }
    }

    public function btcToUsdRateBK()
    {
        try
        {
            $url = "https://api.coingecko.com/api/v3/simple/price?ids=bitcoin&vs_currencies=usd";

            // Initialize cURL
            $ch = curl_init();

            // Set cURL options
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // Execute the request
            $response = curl_exec($ch);

            // Close cURL
            curl_close($ch);

            // Decode the JSON response
            $data = json_decode($response, true);

            // Get the BTC to USD rate
            $rate = $data['bitcoin']['usd'] ?? 0;

            if($rate === 0)
            {
                return response()->json([
                    'status' => false,
                    'rate' => 0,
                    'message' => "API Service Unavailable.",
                    'data' => array()
                ],500);
            }

            return response()->json([
                'status' => true,
                'message' => "BTC USD value calculated successfully.",
                'data' => $rate,
            ]);

        } catch(Exception $e){
            // Log the error
            Log::error('Error in getting BTC to USD rate: ', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => false,
                'message' => "Something went wrong!!!",
                'data' => array()
            ],500);
        }
    }
}
