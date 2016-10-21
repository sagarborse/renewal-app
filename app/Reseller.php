<?php

namespace app;


class Reseller extends ApiClient
{
    public static function getResellerDetails($resellerId = null)
    {
        $resellerId = is_null($resellerId) ? \Config::get('api.auth-userid') : $resellerId;
        if (\Cache::has('resellers/details.json')) {
            $responseJson = \Cache::get('resellers/details.json');
        } else {
            $client = new Reseller();
            $response = $client->get('resellers/details.json', ['query' => ['reseller-id' => $resellerId]]);
            $responseJson = $response->getBody()->getContents();
            \Cache::forever('resellers/details.json', $responseJson);
        }

        return $responseJson;
    }

}
