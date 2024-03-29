<?php

namespace App\Http\Controllers\front;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use App\Cart;
use App\Addons;
use App\Promocode;
use App\User;
use App\About;
use App\Order;
use App\Item;
use App\Time;
use App\Payment;
use App\Address;
use DateTime;
use Auth;
use Storage;
use App;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $user_id  = Session::get('id');
        $getabout = About::where('branch_id','=',@$_COOKIE['branch'])->first();
        $cartdata=Cart::with('itemimage')->select('id','qty','price','item_notes','cart.variation','item_name','tax',\DB::raw("CONCAT('".url('/storage/app/public/images/item/')."/', item_image) AS item_image"),'item_id','addons_id','addons_name','addons_price')
        ->where('user_id',$user_id)
        ->where('branch_id','=',@$_COOKIE['branch'])
        ->where('is_available','=','1')->get();

        $getpromocode=Promocode::select('offer_name','offer_code','offer_amount','description')
        ->where('is_available','=','1')
        ->where('branch_id','=',@$_COOKIE['branch'])
        ->get();

        $addressdata=Address::where('user_id',Session::get('id'))->orderBy('id', 'DESC')->get();

        $userinfo=User::select('name','email','mobile','wallet')->where('id',$user_id)
        ->get()->first();

        $taxval=User::select('currency','map')->where('type','1')->first();

        $getdata=User::select('max_order_qty','min_order_amount','max_order_amount')->where('type','1')->first();

        $getpaymentdata=Payment::select('payment_name','test_public_key','live_public_key','environment')->where('is_available','1')->orderBy('id', 'DESC')->get();

        $branch=User::select('id','name',\DB::raw("CONCAT('".url('/public/images/profile/')."/', profile_image) AS profile_image"))
        ->where('type','=','4')
        ->where('is_available','=','1')
        ->get();

        $getinfo = About::select('logo','footer_logo','favicon','fb','twitter','insta','android','ios','title','short_title','og_image','og_title','og_description','copyright')->where('id','1')->first();

        return view('front.cart', compact('cartdata','getabout','getpromocode','taxval','userinfo','getdata','getpaymentdata','addressdata','branch','getinfo'));
    }

    public function applypromocode(Request $request)
    {
        if($request->promocode == ""){
            return response()->json(["status"=>0,"message"=>trans('messages.promocode')],200);
        }

        $user_id  = Session::get('id');

        $checkpromo=Order::select('promocode')->where('branch_id',@$_COOKIE['branch'])->where('promocode',$request->promocode)->where('user_id',$user_id)
        ->count();

        if ($checkpromo > "0" ) {
            return response()->json(['status'=>0,'message'=>trans('messages.once_per_user')],200);
        } else {
            $promocode=Promocode::select('offer_amount','description','offer_code')->where('offer_code',$request['promocode'])
            ->where('branch_id',@$_COOKIE['branch'])
            ->first();

                session ( [ 
                    'offer_amount' => $promocode->offer_amount, 
                    'offer_code' => $promocode->offer_code,
                ] );

            if($promocode['offer_code']== $request->promocode) {
                if(!empty($promocode))
                {
                    return response()->json(['status'=>1,'message'=>trans('messages.promocode_applied'),'data'=>$promocode],200);
                }
            } else {
                return response()->json(['status'=>0,'message'=>trans('messages.wrong_promocode')],200);
            }
        }
    }

    public function removepromocode(Request $request)
    {
        
        $remove = session()->forget(['offer_amount','offer_code']);

        if(!$remove) {
            return response()->json(['status'=>1,'message'=>trans('messages.promocode_removed')],200);
        } else {
            return response()->json(['status'=>0,'message'=>trans('messages.wrong')],200);
        }
    }

    public function qtyupdate(Request $request)
    {
        if($request->cart_id == ""){
            return response()->json(["status"=>0,"message"=>trans('messages.cart_id_required')],400);
        }
        if($request->item_id == ""){
            return response()->json(["status"=>0,"message"=>trans('messages.item_required')],400);
        }
        if($request->qty == ""){
            return response()->json(["status"=>0,"message"=>trans('messages.qty_required')],400);
        }

        $data=Item::where('item.id', $request['item_id'])
        ->get()
        ->first();

        $cartdata=Cart::where('cart.id', $request['cart_id'])
        ->get()
        ->first();

        $getdata=User::select('max_order_qty','min_order_amount','max_order_amount')->where('type','1')
        ->get()->first();

        if ($getdata->max_order_qty < $request->qty) {
          return response()->json(['status'=>0,'message'=>trans('messages.maximum_purchase')],200);
        }

        $arr = explode(',', $cartdata->addons_id);
        $d = Addons::whereIn('id',$arr)->get();

        $sum = 0;
        foreach($d as $key => $value) {
            $sum += $value->price; 
        }

        if ($request->type == "decreaseValue") {
            $qty = $cartdata->qty-1;
        } else {
            $qty = $cartdata->qty+1;
        }

        $update=Cart::where('id',$request['cart_id'])->update(['item_id'=>$request->item_id,'qty'=>$qty]);

        return response()->json(['status'=>1,'message'=>trans('messages.qty_update')],200);
    }

    public function deletecartitem(Request $request)
    {
        if($request->cart_id == ""){
            return response()->json(["status"=>0,"message"=>trans('messages.cart_id_required')],400);
        }

        $cart=Cart::where('id', $request->cart_id)->delete();

        $count=Cart::where('user_id',Session::get('id'))->count();
        
        Session::put('cart', $count);
        if($cart)
        {
            return 1;
        }
        else
        {
            return 2;
        }
    }

    public function isopenclose(Request $request)
    {
        $getdata=User::select('timezone')->where('type','1')->first();
        date_default_timezone_set($getdata->timezone);

        $date = date('Y/m/d h:i:sa');
        $day = date('l', strtotime($date));

        $isopenclose=Time::where('day','=',$day)->where('branch_id',@$_COOKIE['branch'])->first();

        $current_time = DateTime::createFromFormat('H:i a', date("h:i a"));
        $open_time = DateTime::createFromFormat('H:i a', $isopenclose->open_time);
        $close_time = DateTime::createFromFormat('H:i a', $isopenclose->close_time);

        if ($current_time > $open_time && $current_time < $close_time && $isopenclose->always_close == "2") {
           return response()->json(['status'=>1,'message'=>trans('messages.restaurant_open')],200);
        } else {
           return response()->json(['status'=>0,'message'=>trans('messages.restaurant_closed')],200);
        }
    }

    public function checkitem(Request $request)
    {
        @$user_id  = Session::get('id');
        if (isset($_COOKIE['branch'])) {

            $count=Cart::where('user_id',$user_id)->where('branch_id',@$_COOKIE['branch'])->where('is_available','1')->count();

            Session::put('cart', $count);

            return response()->json(['status'=>1,'cartcnt'=>$count],200);
        } else {
            return response()->json(['status'=>0,'message'=>'Please select branch.'],200);
        }
    }
}
