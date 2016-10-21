<?php

namespace app\Http\Controllers;

use app\Abtest;
use app\Http\Requests;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;

class abTestController extends Controller
{
    public $currentPath;
    public $domain;
    public $id;

    public function determine()
    {
        $url = null;
        $script = 'false';
        $this->currentPath = Request::get('path');
        $this->domain = Request::get('domain');
        $this->id = (int) Request::get('id');

        if (isset($this->id)) {
            $abTestObj = Abtest::where(['id' => $this->id])->get();
        } else {
            $abTestObj = Abtest::where(['domain' => $this->domain, 'path' => $this->currentPath])->get();
        }


        if (!$abTestObj->isEmpty()) {

            if (is_array($abTestObj->all())) {
                $abTestObj = $abTestObj->get(0);
                $url = $this->getUrl($abTestObj);

                $script = $this->getRedirectScript($url);
            }

        }

        $response = Response::make($script);
        $response->header('Content-Type', 'application/javascript');

        return $response;
    }

    public function getRedirectScript($url = null)
    {
        $script = '';
        if (is_null($url)) {
            $script .= 'false';
        } else {
            $script .= 'var query = \'\';';
            $script .= 'if (location.href.indexOf(\'?\') > 0) {';
            $script .= 'query = window.location.href.slice(window.location.href.indexOf(\'?\'));';
            $script .= '}';
            $script .= 'url = \'' . $url . '\' + query;';
            $script .= 'location.href = url';
        }

        return $script;
    }

    public function getUrl($abTestObj)
    {
        if (!is_object($abTestObj)) {
            return null;
        }

        if ($abTestObj->status != 'active') {
            return null;
        }

        $totalVisitors = (int) $abTestObj->visitorCount;
        $totalVisitors++;
        $abTestObj->visitorCount = $totalVisitors;
        $totalShown = (int) $abTestObj->shownCount;
        $abTestObj->shownCount = $totalShown;

        if ($totalShown === 0) {
            $abTestObj->shownCount = 1;
            $abTestObj->save();
            return null;
        }

        $shownPercentage = ($totalShown / $totalVisitors) * 100;
        $targetPercentage = (int) $abTestObj->targetPercent;

        if ($shownPercentage > $targetPercentage) {
            $abTestObj->save();
            return null;
        } elseif ($shownPercentage <= $targetPercentage) {
            $abTestObj->shownCount++;
            $abTestObj->save();
            return $abTestObj->testUrl;
        }
    }
}
