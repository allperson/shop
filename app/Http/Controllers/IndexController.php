<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Goods;
use App\Model\Category;
use App\Http\Requests\UserValidate;
use  App\Model\UserModel;
use App\Model\Cart;
class IndexController extends Controller
{
    //首页
    public function index(Request $request)
    {
        $data=Category::where('pid',0)->get();
        $arr=Goods::get();
        return view('index',['arr'=>$arr,'data'=>$data]);
    }
    //所有商品
    public function allshops(Request $request)
    {
        $id=$request->id;
        $cate=Category::where('pid',0)->get();
        if(empty($id)){
            $arr=Goods::get();
        }else{
            $allid=Category::get();
            $id=intval($id);
            $idall=$this->getID($allid,$id);
            $arr=[];
            foreach($idall as $v){
                $onegoods=Goods::where('cate_id',$v['cate_id'])->first();
                if($onegoods!=''){
                    $arr[]=$onegoods;
                }
            }
        }
       // print_r($arr);die;
        return view('allshops',['cate'=>$cate,'arr'=>$arr,'id'=>$id]);
    }
    //无限极分类
    public function getId($allid,$id)
    {
        static $idall=[];
        foreach($allid as $v){
            if($id==$v['pid']){
                $idall[]=$v;
            }
        }
        return $idall;
    }
    //购物车展示
    public function buycar()
    {
        $id=session('id');
        if(empty($id)){
           return view('login');
        }else{
            $goods_id=Cart::where('user_id',$id)->pluck('goods_id');
            $goods=[];
            $price=0;
            foreach($goods_id as $v){
                $goods[]=Goods::where('goods_id',$v)->first();
                //$price+=Goods::where('goods_id',$v)->value('self_price');
            }

            return view('shopcart',['goods'=>$goods]);
        }
    }
    //添加购物车
    public function cartadd(Request $request)
    {
        $user_id=session('id');
        if(empty($user_id)){
            echo 3;exit;
        }
        $goods_id=$request->goods_id;
        $data=[
            'user_id'=>$user_id,
            'goods_id'=>$goods_id
        ];
        $res=Cart::insert($data);
        if($res){
            echo 1;
        }else{
            echo 2;exit;
        }

    }
    //删除购物车
    public function delcart(Request $request)
    {
        $goods_id=$request->goods_id;
        $res=Cart::where('goods_id',$goods_id)->delete();
        if($res){
            echo 1;
        }else{
            echo 2;exit;
        }
    }
    //个人页
    public function userpage()
    {
        $id=session('id');

        if(empty($id)){
            return view('login');
        }else{
            return view('userpage');
        }
    }
    //商品详情
    public function shopcontent(Request $request)
    {
        $id=$request->id;
        $arr=Goods::where('goods_id',$id)->first();
        return view('shopcontent',['arr'=>$arr]);
    }
    //登陆
    public function login()
    {
        return view('login');
    }
    //登陆执行
    public function logindo(UserValidate $request)
    {
            $request->validated();
            $username=$request->username;
            $pwd=$request->pwd;
            $verifycode=$request->verifycode;
            
            if(session('verifycode')!=$verifycode){
                echo 4;exit;
            }
            $arr=UserModel::where('username',$username)->first();
            if(empty($arr)){
                echo  3;exit;
            }else{
                if($pwd!=decrypt($arr['pwd'])){
                    echo  2;exit;
                }else{
                    $data=[
                        'id'=>$arr['user_id'],
                        'username'=>$username,
                    ];
                    session($data);
                    echo 1;
                }
            }
    }
    //注册
    public function register()
    {
        return view('register');
    }
    //注册执行
    public function registerdo(UserValidate $request)
    {
        $request->validated();
        $arr=$request->all();
        if($arr['code']!=session(['code'])){
            echo 4;exit;
        }
        $re=UserModel::where('username',$arr['username'])->first();
        if(!empty($re)){
            echo 3;exit;
        }
        unset($arr['code']);
        unset($arr['_token']);
        $arr['pwd']=encrypt($arr['pwd']);
        $res=UserModel::insert($arr);
        if($res){
            echo 1;
        }else{
            echo 2;exit;
        }
    }
    //发送短信验证码
    public function sendcode(Request $request)
    {
        $phone=$request->phone;
        $code=rand(1000,9999);
        session(['code'=>$code]);
        $this->code($code,$phone);
    }
    //发送短信验证码
    public function code($code,$mobile)
    {
        $host = env("CODEHOST");
        $path = env("CODEPATH");
        $method = "POST";
        $appcode = env("CODEKEY");
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        $querys = "content=【创信】你的验证码是：".$code."，3分钟内有效！&mobile=".$mobile;
        $bodys = "";
        $url = $host . $path . "?" . $querys;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        if (1 == strpos("$".$host, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        var_dump(curl_exec($curl));
        }
}
