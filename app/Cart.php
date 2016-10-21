<?php

namespace app;


class Cart extends ApiClient
{

    public static function makePayment($invoiceIdsArr, $token, $gatewayId, $redirectUrl, $alternateUrl)
    {
        $resellerId = \Config::get('app.auth-userid');
        $resellerDetailsArr = json_decode(Reseller::getResellerDetails($resellerId), JSON_OBJECT_AS_ARRAY);
        $path = "http://" . $resellerDetailsArr['brandingurl'] . '/servlet/AutoLoginServlet';
        $currency = \Config::get('app.sellingCurrency');
        $noOfTransactions = count($invoiceIdsArr);
        $token = User::getTokenFromCustomerId($token['data']['customerId']);
        $redirectUrl = urlencode($redirectUrl);
        $alternateUrl = urlencode($alternateUrl);

        //$greedy = User::getGreedyTransactions($token['data']['customerId']);

        $form = "<form name='purchase' method='post' action='{$path}' class='form'>";
        $form .= "<input type='hidden' name='CURRENT_URL' value='{$resellerDetailsArr['brandingurl']}'>";
        $form .= "<input type='hidden' name='paymentfor' value='customer'>" .
            "<input type='hidden' name='isPGTransaction' value='true'>";

        $form .= "<input type='hidden' name='fromsupersite' value='true'>" .
            "<input type='hidden' id='token_id' name='userLoginId' value='{$token}'>";

        foreach($invoiceIdsArr as $index => $invoiceId) {
            $form .= "<input type='hidden' name='transid_type' value='{$invoiceId}_invoice' >";
        }

        $form .= "<input type='hidden' name='transactionMode' value='Payment'>";
        $form .= "<input type='hidden' name='custompaymentid' value='{$gatewayId}'>";
        $form .= "<input type='hidden' name='SELLING_CURRENCY_SYMBOL' value='{$currency}'>";
        $form .= "<input type='hidden' name='COMPANY' value='{$resellerDetailsArr['company']}'>";
        $form .= "<input type='hidden' name='nooftrans' value='{$noOfTransactions}'>";
        $form .= "<input type='hidden' name='REDIRECT_URL' value='{$redirectUrl}'>";
        $form .= "<input type='hidden' name='ALTERNATEOPTIONS_URL' value='{$alternateUrl}'>";
        $form .= "</form>";
        $form .= "<script>" .
            "window.onload = function(){document.forms['purchase'].submit();}" .
            "</script>";

        return $form;
    }
}
