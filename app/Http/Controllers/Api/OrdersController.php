<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Orders;
use App\Models\Address;
use App\Models\AddressToOrders;
use App\Models\Clients;
use App\Http\Resources\Orders as OrdersResource;
use App\Http\Resources\Clients as ClientsResource;

class OrdersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function index(Request $request)
    {
        if(json_decode($request->city) && !$request->client) {
            $arr = [];
            foreach (json_decode($request->city) as $key => $value) {
                $arr[] = $value->city_id;
            }
            $orders = Orders::with('clients', 'orderAddress', 'clients.users', 'tasks', 'orderManyAddress.address.entrances')->get()->whereIn('clients.city_id', $arr); 
        }
        else if(!json_decode($request->city) && !$request->client) {
            $orders = Orders::with('clients', 'orderAddress', 'clients.users', 'tasks', 'orderManyAddress.address.entrances')->get();  
        }
        else if($request->client) {
            if(json_decode($request->city)) {
                $arr = [];
                foreach (json_decode($request->city) as $key => $value) {
                    $arr[] = $value->city_id;
                }
                $orders = Orders::with('clients', 'orderAddress', 'clients.users', 'tasks', 'orderManyAddress.address.entrances')->where('clients_id', $request->client)->get()->whereIn('clients.city_id', $arr); 
            }

            if(!json_decode($request->city)) {
                $orders = Orders::with('clients', 'orderAddress', 'clients.users', 'tasks', 'orderManyAddress.address.entrances')->where('clients_id', $request->client)->get();  
            }
        }
        else {
            $orders = Orders::with('clients', 'orderAddress', 'clients.users', 'tasks', 'orderManyAddress.address.entrances')->get(); 
        }
        
        return OrdersResource::collection($orders);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $orders = $request->isMethod('put') ? Orders::findOrFail($request['order']['id']) : new Orders;
        
        if(!$request->isMethod('put')) {
            $orders->id = $request->input('id');
            $orders->clients_id = $request['order']['clients_id']['id'];
        } else {
            $orders->clients_id = $request['order']['clients_id'];
        }
        
        $orders->order_start_date = date("Y-m-d 00:00:00", strtotime($request['dateStart']));
        $orders->order_end_date = date("Y-m-d 00:00:00", strtotime($request['dateEnd']));
        $orders->number_photos = !empty($request['order']['number_photos']) ? $request['order']['number_photos'] : null;
        $orders->save();
       
        if($request['address']) {
            foreach ($request['address'] as $key => $value) {
                $torders = new AddressToOrders;
                $torders->id = $request->input('id');
                $torders->order_id = $orders->id;
                $torders->address_id = $value['id'];
                $torders->coordinates = Address::getCoordinates($value['city'].", " . $value['street'] .", ". $value['house_number']);
                $torders->save();
                Address::editEntrances($value, $torders->id);
            }
        }
        
        return new OrdersResource($orders);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $orders = Orders::with('orderAddress')->findOrFail($id);
        return new OrdersResource($orders);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $orders = Orders::findOrFail($id);

        if($orders->delete()) {
            return new OrdersResource($orders);
        }
    }
}
