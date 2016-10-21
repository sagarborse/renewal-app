<?php

namespace app;

class Domains extends ApiClient
{
    public static $privacyUnSupportedDomains = ['dotasia', 'dotca', 'dotcn', 'dotde', 'dotes', 'doteu', 'dothn', 'dotin', 'dotnl', 'thirdleveldotnz', 'dotpro', 'dotru', 'dotsx', 'dottel', 'dotuk', 'domus'];

    protected static $domainPricingArr;

    public static function checkAvailability($domainNames, $tlds, $showAlternate = false)
    {
        $domainsClient = new Domains();

        if (is_array($domainNames)) {
            $domainNames = self::buildQueryArray($domainNames, 'domain-name');
        } elseif (is_string($domainNames)) {
            $domainNames = ['domain-name' => $domainNames];
        }

        if (is_array($tlds)) {
            $tlds = self::buildQueryArray($domainNames, 'tlds');
        } elseif (is_string($tlds)) {
            $tlds = ['tlds' => $tlds];
        }

        $query = array_merge($domainNames, $tlds);

        if ($showAlternate === true) {
            $query['suggest-alternative'] = 'true';
        }


        $response = $domainsClient->get('domains/available.json', ['query' => $query]);
        $responseJson = $response->getBody()->getContents();

        $query['key-word'] = $query['domain-name'];
        unset($query['domain-name']);
        unset($query['suggest-alternative']);
        $premiumResponse = $domainsClient->get('domains/premium/available.json', ['query' => $query]);
        $premiumJson = $premiumResponse->getBody()->getContents();
        $premiumArr = json_decode($premiumJson, JSON_OBJECT_AS_ARRAY);

        $availabilityArr = json_decode($responseJson, JSON_OBJECT_AS_ARRAY);
        $domainsPricingArr = json_decode(Products::getPricing('domains'), JSON_OBJECT_AS_ARRAY);
        self::$domainPricingArr = $domainsPricingArr;

        $responseArr = [];
        $responseArr['primary'] = [];
        $responseArr['suggestions'] = [];
        $responseArr['premium'] = [];

        foreach ($availabilityArr as $key => $val) {

            if (isset($val['classkey']) && isset($domainsPricingArr[$val['classkey']])) {

                if ($val['status'] === 'regthroughothers' || $val['status'] === 'unknown') {
                    $status = 'UnAvailable';
                } else {
                    $status = $val['status'];
                }

                $pricing = $domainsPricingArr[$val['classkey']];
                $parts = explode('.', $key, 2);

                $responseArr['primary'][$key] = [
                    'domainName' => $key,
                    'tld' => $parts[1],
                    'domainKeyword' => $parts[0],
                    'status' => $status,
                    'pricing' => $pricing,
                    'inCart' => false
                ];

            } elseif (!isset($val['classkey'])) {

                foreach ($val as $keyword => $tlds) {

                    if (!is_array($tlds)) {
                        var_dump($val);
                    }

                    foreach ($tlds as $tld => $status) {
                        $responseArr['suggestions'][] = [
                            'domainName' => $keyword . '.' . $tld,
                            'tld' => $tld,
                            'domainKeyword' => $keyword,
                            'status' => 'available',
                            'pricing' => self::getTldPricing($tld),
                            'inCart' => false
                        ];
                    }
                }
            }
        }


        foreach ($premiumArr as $key => $pricing) {
            $parts = explode('.', $key, 2);
            $responseArr['premium'][] = [
                'domainName' => $key,
                'tld' => $parts[1],
                'domainKeyword' => $parts[0],
                'status' => 'premium',
                'pricing' => array_merge(['premium' => $pricing], self::getTldPricing($parts[1])),
                'inCart' => false
            ];
        }


        return $responseArr;
    }

    public static function getTldPricing($tld)
    {
        $pricing = false;
        $domainsPricingArr = self::$domainPricingArr;
        // hacky implementation of mapping
        if (isset($domainsPricingArr['dot' . $tld])) {
            $pricing = $domainsPricingArr['dot' . $tld];
        } elseif (isset($domainsPricingArr['dom' . $tld])) {
            $pricing = $domainsPricingArr['dom' . $tld];
        } elseif ($tld === 'com' && isset($domainsPricingArr['domcno'])) {
            $pricing = $domainsPricingArr['domcno'];
        } elseif (strpos($tld, '.') !== false) {
            $tld = end(explode('.', $tld));
            if (isset($domainsPricingArr['thirdlevel' . $tld])) {
                $pricing = $domainsPricingArr['thirdlevel' . $tld];
            }
        }

        return $pricing;
    }

    /**
     * @param $productKey
     * @return bool
     */
    public static function isPrivacySupported($productKey)
    {
        return !isset(self::$privacyUnSupportedDomains[$productKey]);
    }

    /**
     * @param $orderId
     * @param $productKey
     * @param $tenure int Tenure in years
     * @param $expiresOn int ExpiresOn as epoch timestamp
     * @param bool|false $includePrivacy
     * @param string $invoiceOption
     */
    public static function renew($orderId, $productKey, $tenure, $expiresOn, $includePrivacy = false, $invoiceOption = 'OnlyAdd')
    {
        if ($includePrivacy === true && !self::isPrivacySupported($productKey)) {
            $includePrivacy = false;
        }
        $query = ['order-id' => $orderId, 'years' => $tenure, 'exp-date' => $expiresOn, 'purchase-privacy' => $includePrivacy, 'invoice-option' => $invoiceOption];
        $client = new Domains();
        $response = $client->post('domains/renew.json', ['query' => $query]);
        $json = $response->getBody()->getContents();

        return $json;
    }
}
