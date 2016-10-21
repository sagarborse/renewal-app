<?php

namespace app\Http\Controllers;

use app\Cart;
use app\Domains;
use app\Http\Requests;
use app\JwtAuth;
use app\Products;
use app\User;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class CartController extends Controller
{
    public function payment()
    {
        $invoiceIdsArr = [];
        $token = Request::get('token');
        try {
            $token = JwtAuth::validate($token);
            $token = json_decode(json_encode($token),true);
            session(['customer.token' => $token]);
        } catch (\Exception $e) {
            return Response::make(['status' => 'ERROR', 'message' => $e->getMessage()], 403);
        }

        $paymentMethodArr = json_decode(Request::get('paymentMethod'), JSON_OBJECT_AS_ARRAY);
        $cartJson = Request::get('cart');
        session(['customer.cartJson' => $cartJson]);
        session(['customer.grandTotal' => Request::get('grandTotal')]);
        Session::save();
        $cartArr = json_decode($cartJson, JSON_OBJECT_AS_ARRAY);

        $invoiceCacheKey = $token['data']['customerId'] . '/invoiceIds';

        if (\Cache::has($invoiceCacheKey)) {

            $pendingInvoiceIdsArr = \Cache::get($invoiceCacheKey);

            if (!empty($pendingInvoiceIdsArr)) {
                foreach ($pendingInvoiceIdsArr as $index => $invoiceId) {
                    $cancelResponse = json_decode(User::cancelInvoice($invoiceId), JSON_OBJECT_AS_ARRAY);

                    if (isset($cancelResponse['status'])) {
                        $message = isset($cancelResponse['message']) ? $cancelResponse['message'] : 'cancel failed';
                        \Log::info($message);
                    }

                    $newInvoiceArr = array_slice($pendingInvoiceIdsArr, 1);

                    \Cache::forever($invoiceCacheKey, $newInvoiceArr);
                }

                \Cache::forget($invoiceCacheKey);
            }

        }

        if (empty($invoiceIdsArr)) {

            foreach ($cartArr['items'] as $item) {

                if ($item['productCategory'] !== 'domains') {
                    $responseArr = json_decode(Products::doAction($item['transactionType'], $item['selectedTenure'], $item['productCategory'], $item['productType'], $item['location'], $item['planId'], $item['orderId'], $item['domainName']), JSON_OBJECT_AS_ARRAY);
                } else {
                    $tenureInYears = $item['selectedTenure'] / 12;
                    $expiresOnEpoch = $item['expiresOn'] / 1000;
                    $responseArr = json_decode(Domains::renew($item['orderId'], $item['productKey'], $tenureInYears, $expiresOnEpoch), JSON_OBJECT_AS_ARRAY);
                }

                if (!isset($responseArr['status']) || (isset($responseArr['status']) && $responseArr['status'] === 'Success')) {
                    array_push($invoiceIdsArr, $responseArr['invoiceid']);
                } else {
                    $message = isset($responseArr['message']) === true ? $responseArr['message'] : 'Unable to renew, you may have a pending renew already on your order';
                    $string = json_encode($responseArr);
                    \Log::warning($message);
                    \Log::warning($string);
                    return \Redirect::action('CartController@show', ['status' => 'error'])->with('errorMessage', $message);
                }
            }

        }

        if ($paymentMethodArr['gatewaytype'] === 'bigrockwallet') {
            $customerWallet = User::getCustomerBalance($token['data']['customerId']);

            if ($customerWallet['totalsellingbalance'] > $cartArr['grandTotal']) {
                $paymentResponseArr = json_decode(User::payViaWallet($invoiceIdsArr), JSON_OBJECT_AS_ARRAY);
                if (!$paymentResponseArr['status']) {
                    return \Redirect::to('/cart/success');
                }
            }

            $message = isset($paymentResponseArr['message']) ? $paymentResponseArr['message'] : 'UnAble to pay using your account balance';
            \Log::warning($message);
            return \Redirect::action('CartController@show', ['status' => 'error'])->with('errorMessage', $message);


        } else {
            //$baseUrl = rtrim(\Config::get('app.baseUrl'), '/');
            $baseUrl = Request::root();
            \Cache::forever($invoiceCacheKey, $invoiceIdsArr);
            $content = Cart::makePayment($invoiceIdsArr, $token, $paymentMethodArr['paymenttypeid'], $baseUrl . '/cart/success', $baseUrl . '/cart/error');
            return Response::make($content);
        }
    }

    /**
     * @param string $status
     */
    public function show($status)
    {
        $status = Request::get('status');

        if (str_contains(strtolower($status), 'success')) {
            $status = 'success';
        } else {
            $status = 'error';
            $params['errorMessage'] = $_POST;
        }

        $params = ['status' => $status, 'pageClass' => 'postPayment'];

        if (Session::has('customer.cartJson')) {
            $params['shoppingCart'] = session('customer.cartJson');
            $params['cartHash'] = md5($params['shoppingCart']);
            $params['timestampHash'] = md5(time());
            $params['shoppingCartArr'] = json_decode($params['shoppingCart'], JSON_OBJECT_AS_ARRAY);
        }

        if (Session::has('customer.token')) {

            $params['token'] = session('customer.token');

            if ( $status === 'success' && Session::has('customer.token')) {
                $token = session('customer.token');
                $paymentLogger = new Logger('paymentLogger');
                $paymentLogger->pushHandler(new StreamHandler(storage_path('logs/payments.log', Logger::INFO)));
                $paymentLogger->addInfo("Start------");
                $paymentLogger->addInfo("customerId: " . $token['data']['customerId']);
                $paymentLogger->addInfo("cartJson: " . json_encode($params['shoppingCartArr'], JSON_PRETTY_PRINT) );
                $paymentLogger->addInfo("End--------");
            }
        }

        if (Session::has('customer.grandTotal')) {
            $params['grandTotal'] = session('customer.grandTotal');
        } else {
            $params['grandTotal'] = '';
        }

        return view('partials.postPayment', $params);
    }
}
