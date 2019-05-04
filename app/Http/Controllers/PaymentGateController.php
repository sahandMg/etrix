<?php

namespace App\Http\Controllers;

use App\Bom;
use App\Repository\URls;
use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use SoapClient;

class PaymentGateController extends Controller
{

    public function testGateway(){

        $price = 100;
        $MerchantID = 'ed8eea3e-068c-11e9-9efd-005056a205be'; //Required
        $verifyUrl = URls::$verify;
        $data = array('MerchantID' => 'ed8eea3e-068c-11e9-9efd-005056a205be',
            'Amount' => $price,
            'Email' => 'sahand.mg.ne@gmail.com',
            'CallbackURL' => 'http://etrix.ir',
            'Description' => 'فروشگاه اینترنتی قطعات الکترونیکی');
        $jsonData = json_encode($data);
        $ch = curl_init('https://www.zarinpal.com/pg/rest/WebGate/PaymentRequest.json');
        curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v1');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ));
        $result = curl_exec($ch);
        $err = curl_error($ch);
        $result = json_decode($result, true);
        curl_close($ch);
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            if ($result["Status"] == '100' ) {
                // $transaction = new Transaction();
                // $transaction->user_id = $user->id;
                // $transaction->order_number = $bom->order_number;
                // $transaction->price = $data['Amount'];
                // $transaction->authority = $result['Authority'];
                // $transaction->status = $result['Status'];
                // $transaction->save();
                print_r($result);
                // header('Location: https://www.zarinpal.com/pg/StartPay/' . $result["Authority"]);
            } else {
                echo'ERR: ' . $result["Status"];
            }
        }

    }

/*
 * Payment Gate
 * Required Params => token , totalPrice , email
 */

    public function Gate(Request $request){

        $user = DB::table('users')->where('token',$request->token)->first();
        if(is_null($user)){
            return '320';
        }
        $bom = Bom::where([['user_id', $user->id],['status',0]])->first();
        $price = $bom->price;
        $MerchantID = 'ed8eea3e-068c-11e9-9efd-005056a205be'; //Required
        $verifyUrl = URls::$verify;
        $data = array('MerchantID' => 'ed8eea3e-068c-11e9-9efd-005056a205be',
            'Amount' => $price,
            'Email' => $user->email,
            'CallbackURL' => $verifyUrl,
            'Description' => 'فروشگاه اینترنتی قطعات الکترونیکی');
        $jsonData = json_encode($data);
        $ch = curl_init('https://www.zarinpal.com/pg/rest/WebGate/PaymentRequest.json');
        curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v1');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ));
        $result = curl_exec($ch);
        $err = curl_error($ch);
        $result = json_decode($result, true);
        curl_close($ch);
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            if ($result["Status"] == '100' ) {
                $transaction = new Transaction();
                $transaction->user_id = $user->id;
                $transaction->order_number = $bom->order_number;
                $transaction->price = $data['Amount'];
                $transaction->authority = $result['Authority'];
                $transaction->status = $result['Status'];
                $transaction->save();
                header('Location: https://www.zarinpal.com/pg/StartPay/' . $result["Authority"]);
            } else {
                echo'ERR: ' . $result["Status"];
            }
        }


    }
// تراکنش رو اول توو جانک ذخیره کن بعدش توو متد verify ببرش تووو transaction


    public function verify(Request $request){

//        if($request->Status == 'NOK') {
//            Transaction::where('user_id', Auth::id())->orderBy('created_at', 'decs')->first()->delete();
//
//            if (isset(Url::where('ip', request()->ip())->first()->pageUrl)) {
//                $page = Url::where('ip', request()->ip())->first()->pageUrl;
//                Url::where('ip', request()->ip())->first()->delete();
//                return redirect($page)->with(['message' => 'عملیات لغو شد']);
//
//            }
//        }
        $Authority = $_GET['Authority'];
        $transaction = Transaction::where('authority',$Authority)->orderBy('id','decs')->first();
        $user = DB::table('users')->where('id',$transaction->user_id)->first();
        $bom = Bom::where([['user_id', $user->id],['status',0]])->first();
        $data = array('MerchantID' => 'ed8eea3e-068c-11e9-9efd-005056a205be', 'Authority' => $Authority, 'Amount'=>$transaction->price);

        $jsonData = json_encode($data);
        $ch = curl_init('https://www.zarinpal.com/pg/rest/WebGate/PaymentVerification.json');
        curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v1');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ));
        $result = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        $result = json_decode($result, true);
        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            if ($result['Status'] == '100') {
                //echo 'Transation success. RefID:' . $result['RefID'];

                $transaction->completed = 1;
                $transaction->refId = $result['RefID'];
                $transaction->save();


//                return [
//                    'body'=>'عملیات پرداخت با موفقیت انجام شد',
//                    'code' => '200'
//                ];
                $url = URls::$truePayment.'/'.$bom->order_number;
                $bom->update(['status'=>50]);

                try{

                    $userCartData = (DB::table('carts')->where('bom_id',$bom->id)->get());
                }catch (\Exception $exception){

                    return $exception;
                }
                $data = [
                'cart'=>$userCartData
                ];
                Mail::send('cart',$data,function($message){

                });
                return redirect($url);

            } else {
// echo 'Transation failed. Status:' . $result['Status'];
//	return redirect()->route('credit',['username'=>$request->username])->with(['Error'=>'تراکنش موفقیت آمیز نبود']);
//                switch($result['Status']){
//
//                    case 'NOK':
//                        return ['body'=>'هيچ نوع عمليات مالي براي اين تراكنش يافت نشد￼','code'=>404];
//                        break;
//
//                    case '-33':
//
//                        $transaction->delete();
//                        return ['body'=>'رقم تراكنش با رقم پرداخت شده مطابقت ندارد','code'=>404];
//                        break;
//
//                    case '-22':
//                        $transaction->delete();
//                        return ['body'=>'تراكنش نا موفق مي باشد','code'=>404];
//                        break;
//
//                    case '-21':
//                        $transaction->delete();
//                        return ['body'=>'هيچ نوع عمليات مالي براي اين تراكنش يافت نشد￼','code'=>404];
//                        break;
//                    case '-11':
//                        $transaction->delete();
//                        return ['body'=>'درخواست مورد نظر یافت نشد','code'=>404];
//                        break;
//
//                    case '-12':
//                        $transaction->delete();
//                        return ['body'=>'امكان ويرايش درخواست ميسر نمي باشد','code'=>404];
//                        break;
//
//                    case '-3':
//                        $transaction->delete();
//                        return ['body'=>'با توجه به محدوديت هاي شاپرك امكان پرداخت با رقم درخواست شده ميسر نمي باشد','code'=>404];
//                        break;
//
//                    case '-54':
//                        $transaction->delete();
//                        return ['body'=>'درخواست مورد نظر آرشيو شده است','code'=>404];
//                        break;
//
//                }

                $transaction->delete();

                // increase item numbers

                $carts = $bom->carts;
                for($i=0;$i<count($carts);$i++){
                    $items = array_values(unserialize($carts[$i]->name));
                    // check each item price in a loop
                    for($t=0;$t<count($items);$t++){

                        $quantity = get_object_vars(DB::table('commons')->where('manufacturer_part_number',$items[$t]['keyword'])->first())['quantity_available'];

                        DB::table('commons')->where('manufacturer_part_number',$items[$t]['keyword'])->update(['quantity_available'=>$quantity + $items[$t]['num'] ]);
                    }

                    $items = [];
                }


                return redirect(URls::$falsePayment.'/'.$bom->order_number);
//                return ['Error'=>'تراکنش ناموفق بود','code'=>404];

            }
        }



    }

    public function GatewayTest(Request $request){


        $user = DB::table('users')->where('token',$request->token)->first();
        if(is_null($user)){
            return '320';
        }

        $bom = Bom::where([['user_id', $user->id],['status',0]])->first();
//        $client = new SoapClient('https://sandbox.zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);
//        $result = $client->PaymentRequest(
//            [
//                'MerchantID' => 'ed8eea3e-068c-11e9-9efd-005056a205be',
//                'Amount' => $bom->price,
//                'Email' => $user->email,
//                'CallbackURL' => 'http://etrix.ir/verify-test',
//                'Description' => 'فروشگاه اینترنتی قطعات الکترونیکی'
//            ]
//        );

//Redirect to URL You can do it also by creating a form
//        if ($result->Status == 100) {
            $transaction = new Transaction();
            $transaction->user_id = $user->id;
            $transaction->order_number = $bom->order_number;
            $transaction->price = $bom->price;
            $num = rand(1000,100000);
            $transaction->authority = $num;
            $transaction->status = 100;
            $transaction->save();
//            Header('Location: https://sandbox.zarinpal.com/pg/StartPay/'.$result->Authority);
//        } else {
//            echo'ERR: '.$result->Status;
//        }
        return $this->verifyGatewayTest($num);

    }

    public function verifyGatewayTest($num){

//        $Authority = $_GET['Authority'];
        $transaction = Transaction::where('authority',$num)->orderBy('id','decs')->first();
        $user = DB::table('users')->where('id',$transaction->user_id)->first();
        $bom = Bom::where([['user_id', $user->id],['status',0]])->first();
        $MerchantID = 'ed8eea3e-068c-11e9-9efd-005056a205be';
        $Amount = 100; //Amount will be based on Toman
//        $Authority = $_GET['Authority'];

//        if ($_GET['Status'] == 'OK') {

//            $client = new SoapClient('https://sandbox.zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);
//
//            $result = $client->PaymentVerification(
//                [
//                    'MerchantID' => $MerchantID,
//                    'Authority' => $Authority,
//                    'Amount' => $Amount,
//                ]
//            );
//            if ($result->Status == 100) {

                $transaction->completed = 1;
                $transaction->refId = 22222;
//                $transaction->refId = $result['RefID'];
                $transaction->save();


//                return [
//                    'body'=>'عملیات پرداخت با موفقیت انجام شد',
//                    'code' => '200'
//                ];
                $url = URls::$truePayment.'/'.$bom->order_number;
                $bom->update(['status'=>50]);

                try{

                    $userCartData = (DB::table('carts')->where('bom_id',$bom->id)->get());
                }catch (\Exception $exception){

                    return $exception;
                }
                $data = [
                    'cart'=>$userCartData
                ];
                Mail::send('emails.cart',$data,function($message){

                });
                return redirect($url);

//            } else {
//
//
//                $transaction->delete();
//
//                // increase item numbers
//
//                $carts = $bom->carts;
//                for($i=0;$i<count($carts);$i++){
//                    $items = array_values(unserialize($carts[$i]->name));
//                    // check each item price in a loop
//                    for($t=0;$t<count($items);$t++){
//
//                        $quantity = get_object_vars(DB::table('commons')->where('manufacturer_part_number',$items[$t]['keyword'])->first())['quantity_available'];
//
//                        DB::table('commons')->where('manufacturer_part_number',$items[$t]['keyword'])->update(['quantity_available'=>$quantity + $items[$t]['num'] ]);
//                    }
//
//                    $items = [];
//                }
//
//
//                return redirect(URls::$falsePayment.'/'.$bom->order_number);

//            }
//        } else {
//
//            echo 'Transaction canceled by user';
//        }
    }


}
