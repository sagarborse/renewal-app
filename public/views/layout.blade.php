<!DOCTYPE html>
<html id="bigrockApp" ng-app="bigrockApp" ng-controller="MainCtrl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="initial-scale=1.0,width=device-width">
    <title>BigRock Renewal - @yield('title')</title>
    <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">
    <style type="text/css">
        html, body, .page {
            height: 100%;
        }

        body, .page {
            margin: 0;
            padding: 0;
            width: 100%;
            display: block;
            font-weight: normal;
            font-family: 'Helvetica';
        }

        [ng\:cloak], [ng-cloak], [data-ng-cloak], [x-ng-cloak], .ng-cloak, .x-ng-cloak {
            display: none !important;
        }
    </style>
    <link rel="stylesheet" href="/css/lib/bootstrap.min.css">
    <link rel="stylesheet" href="/css/lib/font-awesome.min.css">
    <link rel="stylesheet" href="/css/bin/main.min.css">

    <script type="text/javascript" src="<{ URL::asset('js/lib/jquery.min.js') }>"></script>
    <script type="text/javascript" src="<{ URL::asset('js/lib/angular.min.js') }>"></script>
    <script type="text/javascript" src="<{ URL::asset('js/bin/app.js') }>"></script>
</head>
<body>
<div class="container-fluid">
    <div ng-include="'../views/partials/header.html'"></div>
    <uib-alert dismiss-on-timeout="6000" type="danger" ng-show="error.msg" close="dismissError()">{{error.msg}}</uib-alert>
    @if (isset($pageClass))
        <div class="page <{$pageClass}>">
            @else
                <div class="page">
                    @endif
                    @yield('content')
                </div>
        </div>
</body>

<!-- Application Dependencies -->
<!-- scripts: lib -->
<!--[if lt IE 9]>
<script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->

<!--<script type="text/javascript" src="js/lib/angular1.2.29.min.js"></script>-->
<script type="text/javascript" src="<{ URL::asset('js/lib/moment.js') }>"></script>
<script type="text/javascript" src="<{ URL::asset('js/lib/angular-route.min.js') }>"></script>
<script type="text/javascript" src="<{ URL::asset('js/lib/angular-animate.min.js') }>"></script>
<script type="text/javascript" src="<{ URL::asset('js/lib/ngStorage.min.js') }>"></script>
<!--<script type="text/javascript" src="js/lib/angular-storage.min.js"></script>-->
<script type="text/javascript" src="<{ URL::asset('js/lib/ui-bootstrap-tpls.min.js') }>"></script>
<!-- END: scripts: lib -->

<!-- Application Scripts -->
<!-- scripts: bin -->
<script type="text/javascript" src="<{ URL::asset('js/bin/filters.js') }>"></script>
<script type="text/javascript" src="<{ URL::asset('js/bin/services.js') }>"></script>
<script type="text/javascript" src="<{ URL::asset('js/bin/directives.js') }>"></script>
<script type="text/javascript" src="<{ URL::asset('js/bin/main.js') }>"></script>
<!-- <script type="text/javascript" src="<{ URL::asset('js/bin/home.js') }>"></script>
<script type="text/javascript" src="<{ URL::asset('js/bin/login.js') }>"></script>
<script type="text/javascript" src="<{ URL::asset('js/bin/renewal.js') }>"></script>
<script type="text/javascript" src="<{ URL::asset('js/bin/cart.js') }>"></script> -->
<script type="text/javascript" src="<{ URL::asset('js/bin/postPayment.js') }>"></script>
<!-- END: scripts: bin -->
</html>