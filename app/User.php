<?php

namespace app;

use Illuminate\Cache\CacheManager;
use Carbon\Carbon;

class User extends ApiClient
{
    public static $fillable = ['customerid', 'username', 'resellerid', 'name', 'useremail', 'customerstatus'];

    public static function getToken($username, $password, $ip)
    {
        $query = ['username' => $username, 'passwd' => $password, 'ip' => $ip];
        $userClient = new User();
        $response = $userClient->get('customers/generate-token.json', ['query' => $query]);
        if ($response->getStatusCode() !== 200) {
            return json_decode($response->getBody()->getContents(), JSON_OBJECT_AS_ARRAY);
        } else {
            return $response->getBody()->getContents();
        }
    }

    public static function authenticate($token)
    {
        $query = ['token' => trim($token, '"')];
        $userClient = new User();
        $response = $userClient->get('customers/authenticate-token.json', ['query' => $query]);
        $responseJson = $response->getBody()->getContents();
        return json_decode($responseJson, JSON_OBJECT_AS_ARRAY);
    }

    public static function login($username, $password, $ip)
    {
        $token = static::getToken($username, $password, $ip);
        if (static::isJson($token)) {
            $array = json_decode($token, JSON_OBJECT_AS_ARRAY);
            if (isset($array['status']) && $array['status'] === 'ERROR') {
                return $array;
            }
        }
        $userArr = static::authenticate($token);
        return $userArr;
    }

    public static function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public static function getPaymentMethods($customerId, $paymentType)
    {
        if (\Cache::has('paymentMethods.json')) {
            $paymentMethodsJson = \Cache::get('paymentMethods.json');
        } else {
            $query = ['customer-id' => $customerId, 'payment-type' => $paymentType];
            $userClient = new User();
            $response = $userClient->get('pg/allowedlist-for-customer.json', ['query' => $query]);
            $paymentMethodsJson = $response->getBody()->getContents();

            if ($response->getStatusCode() === 200) {
                $expires = Carbon::now()->addDays('1');
                \Cache::put('paymentMethods.json', $paymentMethodsJson, $expires);
            }
        }

        return $paymentMethodsJson;
    }

    public static function getCustomerBalance($customerId)
    {
        $query = ['customer-id' => $customerId];
        $userClient = new User();
        $response = $userClient->get('billing/customer-balance.json', ['query' => $query]);
        $customerBalance = $response->getBody()->getContents();

        return $customerBalance;
    }

    public static function getTokenFromCustomerId($customerId, $ip = null)
    {
        $ip = is_null($ip) ? $_SERVER['REMOTE_ADDR'] : $ip;
        $query = ['customer-id' => $customerId, 'ip' => $ip];
        $client = new User();
        $response = $client->get('customers/generate-login-token.json', ['query' => $query]);
        $token = $response->getBody()->getContents();
        return trim($token, '"');
    }


    public static function getGreedyTransactions($customerId)
    {
        $query = ['customer-id' => $customerId];
        $client = new User();
        $response = $client->get('billing/customer-greedy-transactions.json', ['query' => $query]);
        $greedy = $response->getBody()->getContents();

        return $greedy;
    }

    public static function searchTransactions($noOfRecords = 1, $pageNo = 1, $customerIds = null, array $username = null, $transactionType = null, $transactionKey = null, array $transactionId = null, $transactionDescription = null, $balanceType = null, $amtRangeStart = null, $amtRangeEnd = null, $transactionDateStart = null, $transactionDateEnd = null, array $orderBy = null)
    {
        $query = [];
        $query['no-of-records'] = $noOfRecords;
        $query['page-no'] = $pageNo;
        if (!is_null($customerIds)) {
            $query['customer-id'] = $customerIds;
        }
        if (!is_null($username)) {
            $query['username'] = $username;
        }
        if (!is_null($transactionType)) {
            $query['transaction-type'] = $transactionType;
        }
        if (!is_null($transactionKey)) {
            $query['transaction-key'] = $transactionKey;
        }
        if (!is_null($transactionId)) {
            $query['transaction-id'] = $transactionId;
        }
        if (!is_null($transactionDescription)) {
            $query['transaction-description'] = $transactionDescription;
        }
        if (!is_null($balanceType)) {
            $query['balance-type'] = $balanceType;
        }
        if (!is_null($amtRangeStart) && !is_null($amtRangeEnd)) {
            $query['amt-range-start'] = $amtRangeStart;
            $query['amt-range-end'] = $amtRangeEnd;
        }
        if (!is_null($transactionDateStart) && !is_null($transactionDateEnd)) {
            $query['transaction-date-start'] = $transactionDateStart;
            $query['transaction-date-end'] = $transactionDateEnd;
        }
        if (!is_null($orderBy)) {
            $query['order-by'] = $orderBy;
        }

        $queryHash = md5(serialize($query));

        if (\Cache::has($queryHash . '/customer-transactions')) {
            $json = \Cache::get($queryHash . '/customer-transactions');
        } else {
            $client = new User();
            $response = $client->get('billing/customer-transactions/search.json', ['query' => $query]);
            $json = $response->getBody()->getContents();

            if ($response->getStatusCode() === 200) {
                $expires = Carbon::now()->addMinutes('30');
                \Cache::put($queryHash . '/customer-transactions', $json, $expires);
            }
        }

        return $json;
    }

    public static function getOrders($customerId, $status = 'Active', $noOfRecords = 10, $pageNo = 1)
    {
        $query = [
            'customer-id' => $customerId,
            'status' => $status,
            'no-of-records' => $noOfRecords,
            'page-no' => $pageNo
        ];

        $client = new User();
        $response = $client->get('customers/list-all-orders.json', ['query' => $query]);
        $json = $response->getBody()->getContents();

        return $json;
    }

    public static function cancelInvoice($invoiceIds)
    {
//        if (is_int($invoiceIds)) {
//            $invoiceIds = [$invoiceIds];
//        }

        $query = ['invoice-ids' => $invoiceIds];
        $client = new User();
        $response = $client->post('billing/customer-transactions/cancel.json', ['query' => $query]);
        $json = $response->getBody()->getContents();

        return $json;
    }

    public static function payViaWallet($invoiceIds)
    {
        if (is_int($invoiceIds)) {
            $invoiceIds = [$invoiceIds];
        }

        $query = ['invoice-ids' => $invoiceIds];
        $client = new User();
        $response = $client->post('billing/customer-pay.json', ['query' => $query]);
        $json = $response->getBody()->getContents();

        return $json;
    }

    /**
     * @param $customerId
     * @return array|mixed
     */
    public static function getPendingInvoices($customerId)
    {
        $pendingInvoicesArr = [];
        $responseArr = json_decode(self::searchTransactions(1,1, $customerId, null, 'invoice', null, null, null, 'onlyunbalanced'), JSON_OBJECT_AS_ARRAY);

        if (!isset($responseArr['status'])) {
            foreach ($responseArr as $index => $details) {
                if (isset($details['customer_transaction.transid'])) {
                    $pendingInvoicesArr[] = $details['customer_transaction.transid'];
                }
            }
        } else {
            $pendingInvoicesArr = $responseArr;
        }

        return $pendingInvoicesArr;
    }


    public static function resetPassword($email)
    {
        $query = ['email' => $email];
        $client = new User();
        $response = $client->post('customers/forgot-password.json', ['query' => $query]);
        $bool = $response->getBody()->getContents();

        return $bool;
    }
}
