<!DOCTYPE html>
<html>
    <head>
        <title>BigRock Middleware</title>
        <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="/css/lib/bootstrap.min.css">
        <link rel="stylesheet" href="/css/bin/main.min.css">
    </head>
    <body ng-app="bigrockApp" ng-controller="MainCtrl">
        <div class="container table">
            <div class="contentWrp">
                <h1 class="title">Search for you Domain Name:</h1>
                <div class="searchBox">
                    <input type="text" name="domainNames" ng-model="domains.formData.domainNames" />
                    <a href="#" class="btn btn-primary" ng-click="checkAvailability()">Search</a>
                </div>
                <div class="resultWrp">
                    <ul class="primaryWrp">
                        <li ng-repeat="primaryDomain in domains.primaryCollection">
                           <div class="col-sm-5">{{primaryDomain.domainName}}</div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </body>

    <!-- Application Dependencies -->
    <script type="text/javascript" src="js/lib/angular.min.js"></script>
    <script type="text/javascript" src="js/lib/angular-animate.min.js"></script>
    <script type="text/javascript" src="js/lib/ui-bootstrap-tpls.min.js"></script>

    <!-- Application Scripts -->
    <script type="text/javascript" src="js/bin/app.js"></script>
    <script type="text/javascript" src="js/bin/services.js"></script>
    <script type="text/javascript" src="js/bin/main.js"></script>
</html>
