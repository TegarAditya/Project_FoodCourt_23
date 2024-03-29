<?php

namespace App\Http\Controllers\front;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Category;
use App\Item;
use App\ItemImages;
use App\Ingredients;
use App\Favorite;
use App\Cart;
use App\About;
use App\User;
use App\Addons;
use Illuminate\Support\Facades\Session;
use URL;

class ItemController extends Controller
{
    /**3
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        if (isset($_COOKIE['branch'])) {
            $getcategory = Category::where('branch_id','=',$_COOKIE['branch'])->where('is_available','=','1')->where('is_deleted','2')->get();
            
            $user_id  = Session::get('id');
            $getitem = Item::with(['category','itemimage','variation'])->select('item.cat_id','item.id','item.item_name','item.item_description',DB::raw('(case when favorite.item_id is null then 0 else 1 end) as is_favorite'))
            ->leftJoin('favorite', function($query) use($user_id) {
                $query->on('favorite.item_id','=','item.id')
                ->where('favorite.user_id', '=', $user_id);
            })
            ->where('item.branch_id','=',$_COOKIE['branch'])
            ->where('item.item_status','1')->where('item.is_deleted','2')
            ->orderBy('id', 'DESC')->paginate(9);
            $getabout = About::where('branch_id','=',$_COOKIE['branch'])->first();
        } else {
            $getcategory = array();
            $getitem = array();
            $getabout = "";
        }
        
        $getdata=User::select('currency')->where('type','1')->first();
        $branch=User::select('id','name',\DB::raw("CONCAT('".url('/public/images/profile/')."/', profile_image) AS profile_image"))
        ->where('type','=','4')
        ->where('is_available','=','1')
        ->get();

        $getinfo = About::select('logo','footer_logo','favicon','fb','twitter','insta','android','ios','title','short_title','og_image','og_title','og_description','copyright')->where('id','1')->first();

        return view('front.product',compact('getcategory','getabout','getitem','getdata','branch','getinfo')); 
    }

    public function productdetails(Request $request) {
        $user_id  = Session::get('id');
        $getabout = About::where('branch_id','=',$_COOKIE['branch'])->first();

        if (isset($_COOKIE['branch'])) {
            $getitem = Item::with('category')->select('item.*',DB::raw('(case when favorite.item_id is null then 0 else 1 end) as is_favorite'))
            ->leftJoin('favorite', function($query) use($user_id) {
                $query->on('favorite.item_id','=','item.id')
                ->where('favorite.user_id', '=', $user_id);
            })
            ->where('item.id','=',$request->id)
            ->where('item.item_status','1')
            ->where('item.is_deleted','2')
            ->where('item.branch_id','=',$_COOKIE['branch'])
            ->first();
        }

        if(empty($getitem)){ 
            abort(404); 
        } else {

            $arr = explode(',', $getitem->addons_id);
            foreach ($arr as $value) {
                $freeaddons['value'] = Addons::whereIn('id',$arr)
                ->where('is_available','=','1')
                ->where('is_deleted','=','2')
                ->where('price','=','0')
                ->where('branch_id',$_COOKIE['branch'])
                ->get();
            };
            foreach ($arr as $value) {
                $paidaddons['value'] = Addons::whereIn('id',$arr)
                ->where('is_available','=','1')
                ->where('is_deleted','=','2')
                ->where('price','!=',"0")
                ->where('branch_id',$_COOKIE['branch'])
                ->get();
            };

            $irr = explode(',', $getitem->ingredients_id);
            foreach ($irr as $value) {
                $getingredients['value'] = Ingredients::select(\DB::raw("CONCAT('".url('/storage/app/public/images/ingredients/')."/', image) AS image"))->whereIn('id',$irr)->where('branch_id',$_COOKIE['branch'])->get();
            };

            $getimages = ItemImages::select(\DB::raw("CONCAT('".url('/storage/app/public/images/item/')."/', image) AS image"))->where('item_id','=',$request->id)->where('branch_id',$_COOKIE['branch'])->get();

            $getcategory = Item::where('id','=',$request->id)->where('branch_id',$_COOKIE['branch'])->first();

            $user_id  = Session::get('id');
            $relatedproduct = Item::with(['category','itemimage','variation'])->select('item.cat_id','item.id','item.item_name','item.item_description',DB::raw('(case when favorite.item_id is null then 0 else 1 end) as is_favorite'))
            ->leftJoin('favorite', function($query) use($user_id) {
                $query->on('favorite.item_id','=','item.id')
                ->where('favorite.user_id', '=', $user_id);
            })
            ->where('item.item_status','1')
            ->where('item.is_deleted','2')
            ->where('item.branch_id','=',$_COOKIE['branch'])
            ->where('cat_id','=',$getcategory->cat_id)
            ->where('item.id','!=',$request->id)
            ->orderBy('id', 'DESC')
            ->get();
        }

        $branch=User::select('id','name',\DB::raw("CONCAT('".url('/public/images/profile/')."/', profile_image) AS profile_image"))
        ->where('type','=','4')
        ->where('is_available','=','1')
        ->get();
        $getdata=User::select('currency')->where('type','1')->first();

        $getinfo = About::select('logo','footer_logo','favicon','fb','twitter','insta','android','ios','title','short_title','og_image','og_title','og_description','copyright')->where('id','1')->first();
        return view('front.product-details', compact('getitem','getabout','getimages','getingredients','freeaddons','paidaddons','relatedproduct','getdata','branch','getinfo'));
    }

    public function show(Request $request)
    {
        if (isset($_COOKIE['branch'])) {
            $getcategory = Category::where('branch_id','=',$_COOKIE['branch'])->where('is_available','=','1')->where('is_deleted','2')->get();
            
            $user_id  = Session::get('id');
            $getitem = Item::with(['category','itemimage','variation'])->select('item.cat_id','item.id','item.item_name','item.item_description',DB::raw('(case when favorite.item_id is null then 0 else 1 end) as is_favorite'))
            ->leftJoin('favorite', function($query) use($user_id) {
                $query->on('favorite.item_id','=','item.id')
                ->where('favorite.user_id', '=', $user_id);
            })
            ->where('item.branch_id','=',$_COOKIE['branch'])
            ->where('item.cat_id','=',$request->id)
            ->where('item.item_status','1')->where('item.is_deleted','2')
            ->orderBy('id', 'DESC')->paginate(9);
            $getabout = About::where('branch_id','=',$_COOKIE['branch'])->first();
        } else {
            $getcategory = array();
            $getitem = array();
            $getabout = "";
        }

        $getdata=User::select('currency')->where('type','1')->first();
        $branch=User::select('id','name',\DB::raw("CONCAT('".url('/public/images/profile/')."/', profile_image) AS profile_image"))
        ->where('type','=','4')
        ->where('is_available','=','1')
        ->get();

        $getinfo = About::select('logo','footer_logo','favicon','fb','twitter','insta','android','ios','title','short_title','og_image','og_title','og_description','copyright')->where('id','1')->first();

        return view('front.product', compact('getcategory','getitem','getabout','getdata','branch','getinfo'));
    }

    public function favorite(Request $request)
    {
        if($request->user_id == ""){
            return response()->json(["status"=>0,"message"=>trans('messages.user_required')],400);
        }
        if($request->item_id == ""){
            return response()->json(["status"=>0,"message"=>trans('messages.item_required')],400);
        }

        $data=Favorite::where([
            ['favorite.user_id',$request['user_id']],
            ['favorite.item_id',$request['item_id']]
        ])
        ->get()
        ->first();
        try {
            if ($data=="") {
                $favorite = new Favorite;
                $favorite->branch_id =$_COOKIE['branch'];
                $favorite->user_id =$request->user_id;
                $favorite->item_id =$request->item_id;
                $favorite->save();
                return 1;
            } else {
                return 0;
            }            
        } catch (\Exception $e){
            return response()->json(['status'=>0,'message'=>trans('messages.wrong')],200);
        }
    }

    public function unfavorite(Request $request)
    {
        if($request->user_id == ""){
            return response()->json(["status"=>0,"message"=>trans('messages.user_required')],400);
        }
        if($request->item_id == ""){
            return response()->json(["status"=>0,"message"=>trans('messages.item_required')],400);
        }

        $unfavorite=Favorite::where('user_id', $request->user_id)->where('item_id', $request->item_id)->delete();
        if ($unfavorite) {
            return 1;
        } else {
            return 0;
        }
    }

    public function addtocart(Request $request)
    {
        if($request->item_id == ""){
            return response()->json(["status"=>0,"message"=>"Item is required"],400);
        }
        if($request->qty == ""){
            return response()->json(["status"=>0,"message"=>"Qty is required"],400);
        }
        if($request->price == ""){
            return response()->json(["status"=>0,"message"=>"Price is required"],400);
        }
        if($request->variation_id == ""){
            return response()->json(["status"=>0,"message"=>"Variation is required"],400);
        }
        if($request->user_id == ""){
            return response()->json(["status"=>0,"message"=>"User ID is required"],400);
        }
        try {

            $getitem=Item::with('itemimage')->select('item.id','item.item_name','item.tax')
            ->where('item.id',$request->item_id)->first();

            $cart = new Cart;
            $cart->branch_id =$_COOKIE['branch'];
            $cart->item_id =$request->item_id;
            $cart->addons_id =$request->addons_id;
            $cart->qty =$request->qty;
            $cart->price =$request->price;
            $cart->variation_id =$request->variation_id;
            $cart->variation_price =$request->variation_price;
            $cart->variation =$request->variation;
            $cart->user_id =$request->user_id;
            $cart->item_notes =$request->item_notes;
            $cart->item_name =$getitem->item_name;
            $cart->tax =$getitem->tax;
            $cart->item_image =$getitem['itemimage']->image_name;
            $cart->addons_name =$request->addons_name;
            $cart->addons_price =$request->addons_price;
            $cart->save();

            $count=Cart::where('user_id',$request->user_id)->count();

            Session::put('cart', $count);
            return response()->json(['status'=>1,'message'=>'Item has been added to your cart','cartcnt'=>$count],200);

        } catch (\Exception $e){

            return response()->json(['status'=>0,'message'=>'Something went wrong'],400);
        }
    }

    public function search(Request $request)
    {

        if (isset($_COOKIE['branch'])) {
            $getcategory = Category::where('branch_id','=',$_COOKIE['branch'])->where('is_available','=','1')->where('is_deleted','2')->get();
            
            $user_id  = Session::get('id');
            $getitem = Item::with(['category','itemimage','variation'])->select('item.cat_id','item.id','item.item_name','item.item_description',DB::raw('(case when favorite.item_id is null then 0 else 1 end) as is_favorite'))
            ->leftJoin('favorite', function($query) use($user_id) {
                $query->on('favorite.item_id','=','item.id')
                ->where('favorite.user_id', '=', $user_id);
            })
            ->where('item.branch_id','=',$_COOKIE['branch'])
            ->where('item.item_status','1')->where('item.is_deleted','2')
            ->orderBy('id', 'DESC')->paginate(9);
            $getabout = About::where('branch_id','=',$_COOKIE['branch'])->first();
        } else {
            $getcategory = array();
            $getitem = array();
            $getabout = "";
        }

        $getdata=User::select('currency')->where('type','1')->first();
        $branch=User::select('id','name',\DB::raw("CONCAT('".url('/public/images/profile/')."/', profile_image) AS profile_image"))
        ->where('type','=','4')
        ->where('is_available','=','1')
        ->get();

        $getinfo = About::select('logo','footer_logo','favicon','fb','twitter','insta','android','ios','title','short_title','og_image','og_title','og_description','copyright')->where('id','1')->first();
        return view('front.search', compact('getcategory','getabout','getitem','getdata','branch','getinfo'));
    }

    public function searchitem(Request $request)
    {
        if ($request->keyword != "") {
            $getitem = Item::select('id','item_name')
            ->where('item_name','LIKE','%' . $request->keyword . '%')
            ->where('item_status','1')
            ->where('is_deleted','2')
            ->where('branch_id','=',$_COOKIE['branch'])
            ->orderBy('id', 'DESC')
            ->get();

            $output = '';
                         
            if (count($getitem)>0) {
                
                $output = '<ul class="list-group" style="display: block; position: relative; z-index: 1; height: 262px; overflow-y: scroll; overflow-x: hidden;">';
                
                foreach ($getitem as $row){
                    $output .= '<li class="list-group-item"><a href="'.URL::to('product-details/'.$row->id.'').'" style="font-weight: bolder;">'.$row->item_name.'</a></li>';
                }
                
                $output .= '</ul>';
            } else {
               
                $output .= '<li class="list-group-item" style="font-weight: bolder;">'.'No results'.'</li>';
            }
            return $output;
        }
        
    }
}
