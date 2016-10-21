<!DOCTYPE html>
<html ng-app="bigrockApp" id="bigrockApp" ng-controller="MainCtrl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="initial-scale=1.0,width=device-width">
    <title ng-bind="title"></title>
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
</head>
<body>
    <div class="container-fluid">
        <div ng-include="'views/partials/header.html'"></div>
        <div class="globalErr stickyTop" ng-if="alert.message" ng-cloak>
            <uib-alert dismiss-on-timeout="{{alert.timeout}}" type="red" close="dismissError()">{{alert.message}}</uib-alert>
        </div>
        <div class="page {{pageClass}}" ng-view></div>
    </div>
</body>

<!-- Application Dependencies -->
<!-- scripts: lib -->
<!--[if lt IE 9]>
    <script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<script type="text/javascript" src="js/lib/jquery.min.js"></script>
<script type="text/javascript" src="js/lib/moment.js"></script>
<script type="text/javascript" src="js/lib/angular.min.js"></script>
<!--<script type="text/javascript" src="js/lib/angular1.2.29.min.js"></script>-->
<script type="text/javascript" src="js/lib/angular-route.min.js"></script>
<script type="text/javascript" src="js/lib/angular-animate.min.js"></script>
<script type="text/javascript" src="js/lib/ngStorage.min.js"></script>
<!--<script type="text/javascript" src="js/lib/angular-storage.min.js"></script>-->
<script type="text/javascript" src="js/lib/ui-bootstrap-tpls.min.js"></script>
<!-- END: scripts: lib -->

<!-- Application Scripts -->
<!-- scripts: bin -->
<script type="text/javascript" src="js/bin/app.js"></script>
<script type="text/javascript" src="js/bin/filters.js"></script>
<script type="text/javascript" src="js/bin/services.js"></script>
<script type="text/javascript" src="js/bin/directives.js"></script>
<script type="text/javascript" src="js/bin/main.js"></script>
<script type="text/javascript" src="js/bin/home.js"></script>
<script type="text/javascript" src="js/bin/login.js"></script>
<script type="text/javascript" src="js/bin/renewal.js"></script>
<script type="text/javascript" src="js/bin/cart.js"></script>
<script type="text/javascript" src="js/bin/postPayment.js"></script>
<!-- END: scripts: bin -->

<!-- GA Scripts -->
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-13214337-16', 'auto');
  ga('require', 'eventTracker');

</script>
<script async src="https://www.google-analytics.com/analytics.js"></script>
<script async src="/js/lib/autotrack/autotrack.js"></script>
<!-- END: GA Scripts -->

</html>