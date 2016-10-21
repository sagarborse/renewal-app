<?php
/**
 * Created by PhpStorm.
 * User: shalom.s
 * Date: 10/02/16
 * Time: 8:40 PM
 */

namespace app;

use Firebase\JWT\JWT;

class JwtAuth
{
    public static function make($inputData)
    {
        if (isset($inputData['status']) && $inputData['status'] === 'ERROR') {
            return response()->json(['error' => 'invalid_credentials'], 401);
        }

        $tokenId    = base64_encode(sha1(microtime(true).mt_rand(10000,90000)));
        $issuedAt   = time();
        $notBefore  = $issuedAt + \Config::get('jwt.ttl');                      //Adding 10 seconds
        $expire     = $notBefore + \Config::get('jwt.refresh_ttl');             // Adding 60 seconds
        $serverName = \Config::get('app.serverName');
        $algo       = \Config::get('jwt.algo');
        $secretKey  = base64_decode(\Config::get('jwt.secret'));

        $data = [
            'iat' => $issuedAt,         // Issued at: time when the token was generated
            'jti' => $tokenId,          // Json Token Id: an unique identifier for the token
            'iss' => $serverName,       // Issuer
            'nbf' => $notBefore,        // Not before
            'exp' => $expire,           // Expire
            'data' => (array) $inputData
        ];

        $jwt = JWT::encode(
            $data,
            $secretKey,
            $algo
        );

        $unencodedArray = ['token' => $jwt];

        return $unencodedArray;

    }

    public static function validate($token)
    {
        JWT::$leeway = 43200; // must be equal to TTL (need to de-cypher why)
        $algo       = \Config::get('jwt.algo');
        $secretKey  = base64_decode(\Config::get('jwt.secret'));
        $decoded    = JWT::decode($token, $secretKey, [$algo]);
        return $decoded;
    }
}