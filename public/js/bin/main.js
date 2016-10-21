angular.module('bigrockApp')
    .controller('MainCtrl', function ($scope, $rootScope, $location, $cacheFactory, $localStorage, $window, AuthService, Cart, AlertService) {

        $rootScope.currentUser = AuthService.getUser();
        $rootScope.flowType = undefined;
        $rootScope.lastAction = undefined;
        $rootScope.title = "BigRock Middleware";
        
        $scope.pageLoading = false;
        $scope.shoppingCart = Cart.cartObj;
        $localStorage.shoppingCart = Cart.cartObj;

        $scope.dismissError = function () {
            AlertService.clearAlert();
        };

        $scope.logout = function () {
            $rootScope.lastAction = 'logout';
            AuthService.logout(function () {
                $rootScope.currentUser = null;
                Cart.emptyCart();
                $localStorage.shoppingCart = Cart.cartObj;
                var $httpCache = $cacheFactory.get('$http');
                $httpCache.removeAll();
                $window.location.href = '/#/login';
            });
        };
    });