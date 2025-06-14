<?php

namespace App\Http\Controllers;

use App\Models\Rate;
use Illuminate\Http\Request;
use Validator;

class RateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try
        {
            $query = Rate::query();
            if($request->has('device_token'))
            {
                $query->where('device_token',$request->device_token);
            }
            $rates = $query->get();
            return response()->json(['status'=>count($rates)>0, 'data'=>$rates]);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'rate' => 'required|numeric',
                'device_token' => 'required|string',
                'type' => 'required|in:Up,Down',
            ]);

            if($validator->fails()){
                return response()->json(['status'=>false, 'message'=>'The given data was invalid', 'data'=>$validator->errors()],422);
            }

            $checkRate = Rate::where('device_token',$request->device_token)->where('rate',$request->rate)->first();

            if($checkRate)
            {
                return response()->json(['status'=>false, 'rate_id'=>0, 'message'=>'Sorry already the rate has been token'],400);
            }

            $rate = new Rate();
            $rate->device_token = $request->device_token;
            $rate->rate = $request->rate;
            $rate->type = $request->type;
            $rate->save();
            return response()->json(['status'=>true, 'rate_id'=>intval($rate->id), 'message'=>'Successfully a rate has been added']);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Rate  $rate
     * @return \Illuminate\Http\Response
     */
    public function show(Rate $rate)
    {
        return response()->json(['status'=>true, 'data'=>$rate]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Rate  $rate
     * @return \Illuminate\Http\Response
     */
    public function edit(Rate $rate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Rate  $rate
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Rate $rate)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'rate' => 'required|numeric',
                'device_token' => 'required|string',
            ]);

            if($validator->fails()){
                return response()->json(['status'=>false, 'message'=>'The given data was invalid', 'data'=>$validator->errors()],422);
            }

            $checkRate = Rate::where('id','!=',$rate->id)->where('device_token',$request->device_token)->where('rate',$request->rate)->first();

            if($checkRate)
            {
                return response()->json(['status'=>false, 'rate_id'=>0, 'message'=>'Sorry already the rate has been token'],400);
            }

            $rate->rate = $request->rate;
            $rate->type = $request->type;
            $rate->update();
            return response()->json(['status'=>true, 'rate_id'=>intval($rate->id), 'message'=>'Successfully a rate has been added']);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Rate  $rate
     * @return \Illuminate\Http\Response
     */
    public function destroy(Rate $rate)
    {
        try
        {
            $rate->delete();
            return response()->json(['status'=>true, 'message'=>'Successfully the rate has been deleted']);
        }catch(Exception $e){
            return response()->json(['status'=>false, 'code'=>$e->getCode(), 'message'=>$e->getMessage()],500);
        }
    }

    public function deleteAll(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'device_token' => [
                    'required',
                    'string',
                    'exists:rates,device_token',
                ],
            ]);

            if($validator->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'The given data was invalid',
                    'data' => $validator->errors()
                ],422);
            }

            Rate::where('device_token', $request->device_token)->delete();

            return response()->json([
                'status' => true,
                'message' => 'All rate entries deleted successfully.',
            ]);
        } catch(\Exception $e){
            return response()->json([
                'status' => false,
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ],500);
        }
    }
}
