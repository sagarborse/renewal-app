<?php

namespace app;

use GuzzleHttp\Client;

class ApiClient extends Client
{
    protected $baseUri;

    protected $authUserId;

    protected $apiKey;

    public function __construct($config = [])
    {
        $this->baseUri = \Config::get('app.baseApiUrl');
        $this->authUserId = \Config::get('app.auth-userid');
        $this->apiKey = \Config::get('app.api-key');

        $mergedOptions = array_merge_recursive(['base_uri' => $this->baseUri], $config);
        return parent::__construct($mergedOptions);
    }

    public static function buildQueryArray($array, $queryKey, $suffix = '', $prefix = '')
    {
        $build = [];
        foreach ($array as $key => $val) {
            $build[$queryKey] = $prefix . $val . $suffix;
        }

        return $build;
    }

    public function get($uri, $options = [])
    {
        $mergedOptions = array_merge_recursive(['query' => ['auth-userid' => $this->authUserId, 'api-key' => $this->apiKey], 'http_errors' => false], $options);
        return parent::get($uri, $mergedOptions);
    }

    public function post($uri, $options = [])
    {
        $mergedOptions = array_merge_recursive(['query' => ['auth-userid' => $this->authUserId, 'api-key' => $this->apiKey], 'http_errors' => false], $options);
        return parent::post($uri, $mergedOptions);
    }
}
