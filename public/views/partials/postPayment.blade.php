@extends('layout')

@section('content')
    <div class="contentWrp container-fluid" ng-controller="PostPaymentCtrl">

        @if ($status === 'success')
            <div class="glyphicon glyphicon-ok success large"></div>
            <div class="title successTitle">
                Thanks, Your Payment Was <span class="success">Successful</span>
            </div>
            <div class="subTitle successSubTitle">
                Thank you for visiting Bigrock. You should receive your confirmation email shortly.
            </div>
            <div class="tableWrp">
                <table class="table cartList">
                    <tr class="header">
                        <th class="col-md-2">ORDER ID</th>
                        <th class="col-md-2">INVOICE DATE</th>
                        <th class="col-md-4">PRODUCT DESCRIPTION</th>
                        <th class="col-md-2">DURATION</th>
                        <th class="col-md-2">AMOUNT PAID</th>
                    </tr>
                    <tr class="text-center" ng-init="item.addonsExpanded = false"
                        ng-repeat-start="item in paidCart.items">
                        <td class="orderId text-center">
                            {{item.orderId}}
                        </td>
                        <td class="invoiceDate">
                            {{getNow()}}
                        </td>
                        <td class="description text-center">
                            {{getDescription(item)}}
                        </td>
                        <td class="duration">
                            {{getFormattedTenure(item.selectedTenure)}}
                        </td>
                        <td class="total text-center">
                            <span class="webrupee">Rs.</span> {{getItemTotal(item)}}
                        </td>
                    </tr>
                    <tr class="addonTrigger" ng-if="item.addons">
                        <td class="text-center">
                            <a href ng-click="item.addonsExpanded = !item.addonsExpanded">
                                <span>Addons</span>
                                <span ng-if="item.addonsExpanded == false">+</span>
                                <span ng-if="item.addonsExpanded == true">-</span>
                            </a>
                        </td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td ng-show="item.addonsExpanded === false">+ <span class="webrupee">Rs.</span>
                            {{getAddonTotal(item.addons)}}
                        </td>
                    </tr>
                    <tr class="addonWrp" ng-repeat="addon in item.addons"
                        ng-show="item.addonsExpanded === true && item.addons">
                        <td></td>
                        <td></td>
                        <td class="addonName">
                            <span ng-if="addon.name !== 'ssl'">{{addon.name}}</span>
                            <span ng-if="addon.name === 'ssl'">IP</span>
                            <span ng-if="addon.cost">(@ Rs.{{addon.cost}}/mo)</span>
                        </td>
                        <td class="addonTenure"></td>
                        <td class="addonCost" ng-if="addon.cost && addon.name !== 'ssl'"><span
                                    class="webrupee">Rs.</span> {{addon.cost * selectedTenure}}
                        </td>
                        <td class="addonCost" ng-if="!addon.cost || addon.name === 'ssl'">Free</td>
                    </tr>
                    <tr ng-repeat-end></tr>
                    <tr class="grandTotalWrp">
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="text-right"><strong>Grand Total:</strong></td>
                        <td class="grandTotal">
                            <strong><span class="webrupee">Rs.</span> {{paidCart.grandTotal}}</strong>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="linksWrp text-center">
                <p style="padding: 20px 0;">
                    <a href="/#/renewal">Renew other products ></a>
                    <a href="/">Visit Homepage ></a>
                </p>
            </div>
        @endif

        @if ($status === 'error')
            <div class="glyphicon glyphicon-remove error large"></div>
            <div class="title errorTitle">
                Oops, Your Payment Was <span class="danger">Unsuccessful</span>
            </div>
            <div class="subTitle errorSubTitle">
                Looks like something went wrong while making the payment. Try renewing the product once again or contact
                support if still unsuccessful.
            </div>
            @if ( isset($errorMessage) )
                <div class="alert">
                    Message: <{ $errorMessage }>
                </div>
            @endif
            <div class="linksWrp text-center">
                <p>
                    <a href="/#/renewal">Renew Again ></a>
                    <a href="http://www.bigrock.in/support/contact-us.php">Contact Support ></a>
                </p>
            </div>
        @endif


    </div>

    <!-- GA Scripts -->
    <script>
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
      (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

      ga('create', 'UA-13214337-16', 'auto');
      ga('require', 'eventTracker');
      ga('require', 'ecommerce');
      ga('send', 'pageview');

    </script>
    <script async src="https://www.google-analytics.com/analytics.js"></script>
    <script async src="/js/lib/autotrack/autotrack.js"></script>
    <!-- END: GA Scripts -->

    <script type="text/javascript">

        @if (isset( $shoppingCart ))
            var data = "<{ $shoppingCart }>";
            var shoppingCart = JSON.parse(data.replace(/&quot;/g,'"'));
            var paidCart = angular.copy(shoppingCart);

            $(document).ready(function(){

                if (typeof ga !== 'undefined') {

                    ga('ecommerce:addTransaction', {
                        'id': '<{ $cartHash }>_<{ $timestampHash }>',
                        'affiliation': 'BigRock Renewal',
                        'revenue': '<{ $shoppingCartArr['grandTotal'] }>',
                        'shipping': '0',
                        'tax': '0'
                    });

                    @foreach($shoppingCartArr['items'] as $index => $item )
                        ga('ecommerce:addItem', {
                            'id': '<{$item['orderId']}>',
                            'name': '<{$item['productName']}>',
                            @if(isset($item['planId']))
                            'sku': '<{$item['orderId']}>_<{$item['planId']}>',
                            @else
                            'sku': '<{$item['orderId']}>_domain',
                            @endif
                            'category': '<{$item['productCategory']}>',
                            @if($item['productCategory'] !== 'domains')
                            'price': '<{ $item['selectedTenure'] * $item['pricing'][$item['selectedTenure']] }>',
                            @else
                            'price': '<{ $item['selectedTenure'] * ($item['pricing'][$item['selectedTenure']] / 12) }>',
                            @endif
                            'quantity': '1'
                        });
                    @endforeach
                }

                ga('ecommerce:send');
            });

        @endif

    </script>
@endsection
