<?php

namespace app;

use Carbon\Carbon;
use Illuminate\Cache\CacheManager;

class Products extends ApiClient
{

    public static $keyMapping = [
        'singledomainhostinglinuxus' => [
            'productCategory' => 'singledomainhosting',
            'productType' => 'linux',
            'location' => 'us'
        ],
        'singledomainhostinglinuxin' => [
            'productCategory' => 'singledomainhosting',
            'productType' => 'linux',
            'location' => 'in'
        ],
        'singledomainhostinglinuxuk' => [
            'productCategory' => 'singledomainhosting',
            'productType' => 'linux',
            'location' => 'uk'
        ],
        'singledomainhostinglinuxhk' => [
            'productCategory' => 'singledomainhosting',
            'productType' => 'linux',
            'location' => 'hk'
        ],
        'singledomainhostinglinuxtr' => [
            'productCategory' => 'singledomainhosting',
            'productType' => 'linux',
            'location' => 'tr'
        ],
        'singledomainhostingwindowsus' => [
            'productCategory' => 'singledomainhosting',
            'productType' => 'windows',
            'location' => 'us'
        ],
        'singledomainhostingwindowsin' => [
            'productCategory' => 'singledomainhosting',
            'productType' => 'windows',
            'location' => 'in'
        ],
        'singledomainhostingwindowshk' => [
            'productCategory' => 'singledomainhosting',
            'productType' => 'windows',
            'location' => 'hk'
        ],
        'singledomainhostingwindowstr' => [
            'productCategory' => 'singledomainhosting',
            'productType' => 'windows',
            'location' => 'tr'
        ],
        'singledomainhostingwindowsuk' => [
            'productCategory' => 'singledomainhosting',
            'productType' => 'windows',
            'location' => 'uk'
        ],
        'multidomainhosting' => [
            'productCategory' => 'multidomainhosting',
            'productType' => 'linux',
            'location' => 'us'
        ],
        'multidomainhostinglinuxin' => [
            'productCategory' => 'multidomainhosting',
            'productType' => 'linux',
            'location' => 'in'
        ],
        'multidomainhostinglinuxuk' => [
            'productCategory' => 'multidomainhosting',
            'productType' => 'linux',
            'location' => 'uk'
        ],
        'multidomainhostinglinuxhk' => [
            'productCategory' => 'multidomainhosting',
            'productType' => 'linux',
            'location' => 'hk'
        ],
        'multidomainhostinglinuxtr' => [
            'productCategory' => 'multidomainhosting',
            'productType' => 'linux',
            'location' => 'tr'
        ],
        'multidomainwindowshosting' => [
            'productCategory' => 'multidomainhosting',
            'productType' => 'windows',
            'location' => 'us'
        ],
        'multidomainwindowshostingin' => [
            'productCategory' => 'multidomainhosting',
            'productType' => 'windows',
            'location' => 'in'
        ],
        'multidomainwindowshostinguk' => [
            'productCategory' => 'multidomainhosting',
            'productType' => 'windows',
            'location' => 'uk'
        ],
        'multidomainwindowshostinghk' => [
            'productCategory' => 'multidomainhosting',
            'productType' => 'windows',
            'location' => 'hk'
        ],
        'multidomainwindowshostingtr' => [
            'productCategory' => 'multidomainhosting',
            'productType' => 'windows',
            'location' => 'tr'
        ],
        'resellerhosting' => [
            'productCategory' => 'resellerhosting',
            'productType' => 'linux',
            'location' => 'us'
        ],
        'resellerhostinglinuxin' => [
            'productCategory' => 'resellerhosting',
            'productType' => 'linux',
            'location' => 'in'
        ],
        'resellerhostinglinuxuk' => [
            'productCategory' => 'resellerhosting',
            'productType' => 'linux',
            'location' => 'uk'
        ],
        'resellerwindowshosting' => [
            'productCategory' => 'resellerhosting',
            'productType' => 'windows',
            'location' => 'us'
        ],
        'resellerwindowshostingin' => [
            'productCategory' => 'resellerhosting',
            'productType' => 'windows',
            'location' => 'in'
        ],
        'resellerwindowshostinguk' => [
            'productCategory' => 'resellerhosting',
            'productType' => 'windows',
            'location' => 'uk'
        ],
        'vpslinuxus' => [
            'productCategory' => 'vps',
            'productType' => 'linux',
            'location' => 'us'
        ],
        'vpslinuxin' => [
            'productCategory' => 'vps',
            'productType' => 'linux',
            'location' => 'in'
        ],
        'dedicatedserverlinuxus' => [
            'productCategory' => 'dedicatedserver',
            'productType' => 'linux',
            'location' => 'us'
        ],
        'others' => ['codeguard', 'sitelock', 'sslcert', 'thawtcert', 'hosting', 'enterpriseemailus', 'eeliteus', 'impressly']
    ];


    public static function getPricing($product = null)
    {
        if (\Cache::has('pricing.json')) {
            $pricingJson = \Cache::get('pricing.json');
        } else {
            $productObj = new Products();
            $response = $productObj->get('products/customer-price.json');
            $pricingJson = $response->getBody()->getContents();

            if ($response->getStatusCode() === 200) {
                $expires = Carbon::now()->addDays('1');
                \Cache::put('pricing.json', $pricingJson, $expires);
            }
        }

        if (!empty($product) && $product === 'domains') {
            $pricingJson = self::filterDomains($pricingJson);
        }

        return $pricingJson;
    }

    public static function getProductType($productKey)
    {
        if ((strpos($productKey, 'vps') === false && strpos($productKey, 'hosting') === false) || strpos($productKey, 'email') > -1) {
            return null;
        }

        if (isset(self::$keyMapping[$productKey])) {
            return self::$keyMapping[$productKey]['productType'];
        }

        return null;
    }

    public static function getLocation($productKey)
    {
        if ((strpos($productKey, 'vps') === false && strpos($productKey, 'hosting') === false) || strpos($productKey, 'email') > -1) {
            return null;
        }

        if (isset(self::$keyMapping[$productKey])) {
            return self::$keyMapping[$productKey]['location'];
        }

        return null;
    }

    public static function getProductCategory($productKey)
    {
        if (isset(self::$keyMapping[$productKey])) {
            return self::$keyMapping[$productKey]['productCategory'];
        } elseif (in_array($productKey, self::$keyMapping['others'])) {
            return $productKey;
        } else {
            return 'domains';
        }
    }

    public static function getDetails($productKey, $orderId, $productType = null, $location = null)
    {
        if (is_null($productType)) {
            $productType = self::getProductType($productKey);
        }
        if (is_null($location)) {
            $location = self::getLocation($productKey);
        }

        $productCategory = self::getProductCategory($productKey);

        if (\Cache::has($orderId . '/details.json')) {
            $responseJson = \Cache::get($orderId . '/details.json');
        } else {
            $pathArr = [];
            $pathArr[] = $productCategory;
            if (isset($productType)) {
                $pathArr[] = $productType;
            }
            if (isset($location)) {
                $pathArr[] = $location;
            }
            $path = implode('/', $pathArr) . '/details.json';

            $client = new Products();
            $query = ['order-id' => $orderId];
            if ($productCategory === 'domains') {
                $query['options'] = 'OrderDetails';
            }
            $response = $client->get($path, ['query' => $query]);
            $responseJson = $response->getBody()->getContents();

            if ($response->getStatusCode() === 200) {
                $expires = Carbon::now()->addDays('1');
                \Cache::put($orderId . '/details.json', $responseJson, $expires);
            }
        }

        return $responseJson;
    }


    public static function getKeyMapping()
    {
        if (\Cache::has('keyMapping.json')) {
            $keyMappingJson = \Cache::get('keyMapping.json');
        } else {
            $productObj = new Products();
            $response = $productObj->get('products/category-keys-mapping.json');
            $keyMappingJson = $response->getBody()->getContents();

            //$keyMappingCleanJson = self::cleanKeyMap($keyMappingJson);
            if ($response->getStatusCode() === 200) {
                $expires = Carbon::now()->addDays('1');
                \Cache::put('keyMapping.json', $keyMappingJson, $expires);
            }

        }

        return $keyMappingJson;
    }

    protected static function filterDomains($pricingJson)
    {
        $pricingArr = json_decode($pricingJson, JSON_OBJECT_AS_ARRAY);
        $matches = [];
        foreach ($pricingArr as $productKey => $val) {
            if (preg_match('/dot(\w+)/', $productKey) || preg_match('/dom(\w+)/', $productKey) || preg_match(
                    '/thirdleveldot(\w+)/',
                    $productKey
                )
            ) {
                $matches[$productKey] = $val;
            }
        }

        return json_encode($matches);
    }

    protected static function cleanKeyMap($keyMappingJson)
    {
        $keyMappingArr = json_decode($keyMappingJson, JSON_OBJECT_AS_ARRAY);
        $keyMappingCleanArr['hosting'] = $keyMappingArr['hosting'];
        foreach ($keyMappingArr['domorder'] as $index => $val) {

        }
    }

    public static function doAction(
        $action,
        $tenure,
        $productKey,
        $productType = null,
        $location = null,
        $planId = null,
        $orderId = null,
        $domainName = null,
        $invoiceOption = 'OnlyAdd'
    ) {
        $pathArr = [];
        $pathArr[] = $productKey;

        if (isset($productType) && isset($location)) {
            $pathArr[] = $productType;
            $pathArr[] = $location;
        }

        $pathArr[] = $action;

        if ($action === 'renew') {
            $query = ['order-id' => $orderId, 'months' => $tenure, 'invoice-option' => $invoiceOption];
            $client = new Products();
            $path = implode("/", $pathArr) . '.json';
            $response = $client->post($path, ['query' => $query]);
            $responseJson = $response->getBody()->getContents();
        } elseif ($action === 'add') {

        }

        return $responseJson;
    }
}
