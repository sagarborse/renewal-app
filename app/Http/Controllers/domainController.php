<?php

namespace app\Http\Controllers;

use app\Domains;
use app\Products;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;

use app\Http\Requests;
use app\Http\Controllers\Controller;

class DomainController extends Controller
{
    public function index()
    {

    }

    public function showDetails()
    {

    }

    public function showAvailability()
    {
        $domainNames = Request::input('domainNames');
        $domainKeyword = Request::input('domainKeyword');
        $tlds = Request::input('tld');

        if (isset($domainNames)) {
            $domainsData = $this->getKeywordsAndTlds($domainNames);
            $domainKeyword = $domainsData['domainKeywords'];
            $tlds = $domainsData['tlds'];
        } elseif (isset($domainKeyword) && strpos($domainKeyword, '.') !== false) {
            $domainParts = explode('.', $domainKeyword, 2);
            $domainKeyword = $domainParts[0];
            $tlds = $domainParts[1];
        }

        $response = Domains::checkAvailability($domainKeyword, $tlds, true);
        return Response::make($response);
    }

    protected function getKeywordsAndTlds($domainNames)
    {
        $domainKeywords = []; $tlds = [];
        $domainNames = explode(',',$domainNames);
        foreach($domainNames as $domain) {
            $arr = explode('.', $domain, 2);
            $domainKeywords[] = $arr[0];
            $tlds[] = $arr[1];
        }

        return ['domainKeywords' => $domainKeywords, 'tlds' => $tlds];
    }

    public function showPricing()
    {
        $pricing = Products::getPricing();

        return Response::make($pricing);
    }

    public function showKeyMapping()
    {
        $keyMapping = Products::getKeyMapping();

        return Response::make($keyMapping);
    }
}
