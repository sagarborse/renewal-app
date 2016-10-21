angular.module('bigrockApp')
    .controller('RenewalCtrl', function ($scope, $rootScope, $location, AuthService, Orders, Cart, AlertService, TIMEOUT) {

        $rootScope.title = "Bigrock: Renew Your Order";
        $rootScope.flowType = 'renewal';
        $scope.fetchingOrders = true;
        $scope.fetchingError = false;

        Orders.getOrders().then(function (response) {
            $scope.orders = response.orders;
            $scope.fetchingOrders = false;
        }).catch(function (response) {
            $scope.fetchingOrders = false;
            $scope.fetchingError = true;

            // Response Error handling for errors outside the global handling scope
            if (response.status !== 401 && response.status !== 403) {
                var msg = response.message || "Aww Snap! You disturbed the delicate balance of the OrdersKeeper.";
                AlertService.showAlert('error', msg, TIMEOUT)
            }
        });

        $scope.renew = function (product) {
            Cart.addItem(product);
            $location.path('/cart');
        };

        $scope.getDaysLeft = function (date) {
            var expires = Date.parse(date);
            var delta = Math.abs(expires - new Date()) / 1000;
            return Math.floor(delta / 86400);
        };

        $scope.getPopoverContent = function (order) {
            if (order.productCategory === 'impressly') {
                return "You are in a free plan which cannot be renewed. Please upgrade to a paid plan to extend the expiry date.\n";
            } else {
                return "This product currently has no renew options.\n";
            }
        };

        $scope.getPopoverTitle = function (order) {
            return "Why can't I renew this Order: " + order.productName ;
        };
    });