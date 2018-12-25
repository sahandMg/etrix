<?php

namespace App\Http\Controllers;

use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentGateController extends Controller
{
    /*
 * Payment Gate
 * Required Params => token , totalPrice , email
 */

    public function Gate(Request $request){


        $MerchantID = 'ed8eea3e-068c-11e9-9efd-005056a205be'; //Required
        $data = array('MerchantID' => 'ed8eea3e-068c-11e9-9efd-005056a205be',
            'Amount' => 1000,
            'Email' => 's23.moghadam@gmail.com',
            'CallbackURL' => "https://etrix.ir/credit-verify",
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
            if ($result["Status"] == 100) {
                $transaction = new Transaction();
                $transaction->user_id = 1;
                $transaction->price = $request->totalPrice;
                $transaction->authority = $result['Authority'];
                $transaction->save();
                header('Location: https://www.zarinpal.com/pg/StartPay/' . $result["Authority"]);
            } else {
                echo'ERR: ' . $result["Status"];
            }
        }


    }
// تراکنش رو اول توو جانک ذخیره کن بعدش توو متد verify ببرش تووو transaction


    public function verify(Request $request){
        $Authority = $_GET['Authority'];
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
        $transaction = Transaction::where('user_id',1)->orderBy('created_at','decs')->first();
        $data = array('MerchantID' => 'ed8eea3e-068c-11e9-9efd-005056a205be', 'Authority' => $Authority, 'Amount'=>$transaction->money);

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
            echo "cURL Error #:" . $err;
        } else {
            if ($result['Status'] == 100) {
                //echo 'Transation success. RefID:' . $result['RefID'];

                $transaction->completed = 1;
                $transaction->refId = $result['RefID'];
                $transaction->save();
//                $credit = Auth::user()->credit;
//                Auth::user()->update(['credit' => $transaction->money + $credit]);

//                if(isset(Url::where('ip',request()->ip())->first()->pageUrl)){
//                    $page = Url::where('ip',request()->ip())->first()->pageUrl;
//                    Url::where('ip',request()->ip())->first()->delete();
//                    return redirect($page)->with(['message'=>'اعتبار شما با موفقیت افزایش یافت']);
//                }else{
//                    return redirect()->route('home');
//                }

                return [
                    'body'=>'عملیات پرداخت با موفقیت انجام شد',
                    'code' => '200'
                ];


            } else {
// echo 'Transation failed. Status:' . $result['Status'];
//	return redirect()->route('credit',['username'=>$request->username])->with(['Error'=>'تراکنش موفقیت آمیز نبود']);
                switch($result['Status']){

                    case 'NOK':
                        return ['body'=>'هيچ نوع عمليات مالي براي اين تراكنش يافت نشد￼','code'=>404];
                        break;

                    case '-33':

                        $transaction->delete();
                        return ['body'=>'رقم تراكنش با رقم پرداخت شده مطابقت ندارد','code'=>404];
                        break;

                    case '-22':
                        $transaction->delete();
                        return ['body'=>'تراكنش نا موفق مي باشد','code'=>404];
                        break;

                    case '-21':
                        $transaction->delete();
                        return ['body'=>'هيچ نوع عمليات مالي براي اين تراكنش يافت نشد￼','code'=>404];
                        break;

                    case '-12':
                        $transaction->delete();
                        return ['body'=>'امكان ويرايش درخواست ميسر نمي باشد','code'=>404];
                        break;

                    case '-3':
                        $transaction->delete();
                        return ['body'=>'با توجه به محدوديت هاي شاپرك امكان پرداخت با رقم درخواست شده ميسر نمي باشد','code'=>404];
                        break;

                    case '-54':
                        $transaction->delete();
                        return ['body'=>'درخواست مورد نظر آرشيو شده است','code'=>404];
                        break;


                }
                $transaction->delete();
                return ['Error'=>'تراکنش ناموفق بود','code'=>404];

            }
        }



    }


}
