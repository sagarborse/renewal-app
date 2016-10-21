<?php

namespace app\Http\Controllers;

use app\Http\Requests;
use app\JwtAuth;
use app\Products;
use app\User;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;

class CustomerController extends Controller
{
    public function getOrders()
    {
        $auth = Request::header('Authorization');
        list($token) = sscanf($auth, 'Bearer %s');
        try {
            $token = JwtAuth::validate($token);
        } catch (\Exception $e) {
            return Response::make(['status' => 'ERROR', 'message' => $e->getMessage()], 403);
        }

        // Get orders from OBox
        $ordersArr = json_decode(User::getOrders($token->data->customerId), JSON_OBJECT_AS_ARRAY);

        // Build front-end compatible orders list
        $orders = self::buildOrdersList($ordersArr['orderList']);

        $response = Response::make(
            ['orders' => $orders, 'totalCount' => $ordersArr['totalCount'], 'totalPages' => $ordersArr['totalPages']]
        );
        $response->header('Content-Type', 'application/json');
        return $response;
    }

    public function getPaymentMethods()
    {
        $auth = Request::header('Authorization');
        list($token) = sscanf($auth, 'Bearer %s');
        try {
            $token = JwtAuth::validate($token);
        } catch (\Exception $e) {
            return Response::make(['status' => 'ERROR', 'message' => $e->getMessage()], 403);
        }

        $customerId = $token->data->customerId;
        $paymentMethodsArr = json_decode(User::getPaymentMethods($customerId, 'Payment'), JSON_OBJECT_AS_ARRAY);
        $customerBalanceArr = json_decode(User::getCustomerBalance($customerId), JSON_OBJECT_AS_ARRAY);

        $bigrockWallet = [
            'autorenewsupport' => false,
            'gatewayname' => 'Bigrock Wallet',
            'gatewaytype' => 'bigrockwallet',
            'displayposition' => "4"
        ];

        $bigrockWallet = array_merge($bigrockWallet, $customerBalanceArr);
        array_push($paymentMethodsArr, $bigrockWallet);

        return Response::make(array_values($paymentMethodsArr));
    }


    public static function buildOrdersList($orders)
    {
        $ordersList = [];
        $pricingArr = json_decode(Products::getPricing(), JSON_OBJECT_AS_ARRAY);
        foreach ($orders as $index => $order) {
            $ordersList[$index]['productKey'] = $order['product_key'];
            $ordersList[$index]['domainName'] = $order['domainname'];
            $ordersList[$index]['daysToDeletion'] = $order['daysToDeletion'];
            $ordersList[$index]['orderId'] = $order['orderid'];
            $ordersList[$index]['daysToExpiry'] = $order['daysToExpiry'];
            // millisecond convertion
            $ordersList[$index]['createdOn'] = $order['creationtime'] * 1000;
            $ordersList[$index]['deletesOn'] = $order['deltime'] * 1000;
            $ordersList[$index]['expiresOn'] = $order['endtime'] * 1000;
            $ordersList[$index]['productName'] = $order['product_category'];

            $detailsArr = json_decode(
                Products::getDetails($order['product_key'], $order['orderid']),
                JSON_OBJECT_AS_ARRAY
            );

            $productKey = $order['product_key'];
            $ordersList[$index]['productCategory'] = Products::getProductCategory($productKey);
            $ordersList[$index]['productType'] = Products::getProductType($productKey);
            $ordersList[$index]['location'] = Products::getLocation($productKey);

            if ($ordersList[$index]['productCategory'] !== 'domains') {
                $ordersList[$index]['planId'] = $detailsArr['planid'];
                if (isset($pricingArr[$productKey]['plans'][$detailsArr['planid']]['renew'])) {
                    $ordersList[$index]['renew'] = $pricingArr[$productKey]['plans'][$detailsArr['planid']]['renew'];
                    //$ordersList[$index]['add'] = $pricingArr[$productKey]['plans'][$detailsArr['planid']]['add'];
                } elseif (isset($pricingArr[$productKey][$detailsArr['planid']]['renew'])) {
                    $ordersList[$index]['renew'] = $pricingArr[$productKey][$detailsArr['planid']]['renew'];
                    //$ordersList[$index]['add'] = $pricingArr[$productKey][$detailsArr['planid']]['add'];
                } else {
                    $ordersList[$index]['renew'] = null;
                }
            } else {
                foreach ($pricingArr[$productKey]['renewdomain'] as $year => $cost) {
                    $ordersList[$index]['renew'][$year * 12] = $cost;
                }
            }

            if (isset($detailsArr['addons'])) {
                //$ordersArr[$index]['addons'] = $detailsArr['addons'];
                $ordersList[$index]['addons'] = [];
                foreach ($detailsArr['addons'] as $addOnId => $array) {
                    $addon = [];
                    if (isset($pricingArr[$productKey][$detailsArr['planid']][$addOnId])) {
                        $addon['addOnId'] = $addOnId;
                        $addon['name'] = $addOnId;
                        $addon['cost'] = $pricingArr[$productKey][$detailsArr['planid']][$addOnId];
                        //$ordersArr[$index]['addons'][$addOnId]['cost'] = $pricingArr[$productKey][$ordersArr[$index]['planId']][$addOnId];
                        array_push($ordersList[$index]['addons'], $addon);
                    }
                }
            } elseif (isset($detailsArr['installed_os']) && isset($detailsArr['installed_os']['addons'])) {

                foreach ($detailsArr['installed_os']['addons'] as $addonIndex => $addon) {
                    $ordersList[$index]['addons'][$addonIndex]['addOnId'] = isset($addon['addOnID']) ? $addon['addOnID'] : $addon['addOnId'];
                    $ordersList[$index]['addons'][$addonIndex]['name'] = $addon['name'];
                    if (false !== in_array($addon['name'], array_keys($pricingArr[$productKey]['addons']))) {
                        $ordersList[$index]['addons'][$addonIndex]['cost'] = $pricingArr[$productKey]['addons'][$addon['name']];
                    }
                }
            }
        }

        return $ordersList;
    }


    public function authenticate()
    {
        $username = Request::input('email');
        $password = Request::input('password');
        $ip = Request::ip();

        $token = User::getToken($username, $password, $ip);

        if (is_array($token) && isset($token['status']) && $token['status'] === 'ERROR') {
            $content = $token;
            $response = 401;
        } else {
            $userArr = User::authenticate($token);
            $_name = explode(' ', $userArr['name']);
            $fname = isset($_name[0]) ? $_name[0] : 'Unknown';
            $lname = isset($_name[1]) ? $_name[1] : '';
            $userArrGuarded = ['customerId' => $userArr['customerid'], 'resellerId' => $userArr['resellerid'], 'email' => $userArr['useremail'], 'name' => $userArr['name'], 'fname' => $fname, 'lname' => $lname ];
            $content = JwtAuth::make($userArrGuarded);
            $response = 200;
        }

        return Response::make($content, $response);
    }

    public function forgotPassword()
    {
        $username = Request::input('email');
        $response = User::resetPassword($username);
        if ($response === 'true') {
            $status = 'success';
        } else {
            $status = 'error';
        }
        return Response::make(['status' => $status]);
    }

    public static function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

}
