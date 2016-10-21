angular.module('bigrockApp')
    .controller('CartCtrl', function ($scope, $rootScope, $location, $filter, Cart, AlertService, TIMEOUT) {

        $scope.title = 'Bigrock: Renew Cart';
        $scope.selectedMethod = null;
        $scope.selectedTenure = 12;
        $scope.loadingPaymentMethods = true;
        $scope.checkoutBtnTxt = "Pay Securely Now";

        Cart.getPaymentMethods().then(function (response) {
            $scope.loadingPaymentMethods = false;
            $scope.paymentMethods = response.map(function (obj) {
                obj.selected = false;
                return obj;
            });
        }).catch(function (response) {
            // Response Error handling for errors outside the global handling scope
            if (response.status !== 401 && response.status !== 403) {
                var msg = response.message || "Aww Snap!! You disturbed the delicate balance of the CartKeeper!";
                AlertService.showAlert('error', msg, TIMEOUT)
            }
        });

        $scope.selectMethod = function (method) {
            AlertService.clearAlert();
            if (method.gatewaytype === 'bigrockwallet' && method.totalsellingbalance < $scope.getGrandTotal()) {
                AlertService.showAlert('error', 'You do not have sufficient balance to choose this option.', TIMEOUT);
                return false;
            }
            $scope.selectedMethod = null;
            $scope.selectedMethod = method;
        };

        $scope.getIcons = function (paymentMethod, type) {
            var basedir = '/images/', path;
            switch (paymentMethod.gatewaytype) {
                case 'paypal':
                    path = basedir + 'paypal';
                    break;

                case 'pay.pw':
                    path = basedir + 'credit-card';
                    break;

                case 'payu.in':
                    path = basedir + 'netbanking';
                    break;

                case 'bigrockwallet':
                    path = basedir + 'bigrockwallet';
                    break;

                default:
                    path = basedir + 'credit-card';
                    break;
            }

            if (type === 'svg') {
                path = path + '.svg';
            } else {
                path = path + '.png';
            }

            return path;
        };

        $scope.getFormattedTenure = function (tenure) {
            if (tenure >= 24) {
                return tenure / 12 + " Years";
            } else if (tenure >= 12 && tenure < 24) {
                return tenure / 12 + " Year";
            } else if (tenure < 12 && tenure > 1) {
                return tenure + " months";
            } else {
                return tenure + " month";
            }
        };

        $scope.changeTenure = function (cartObj, tenure) {
            cartObj.selectedTenure = $scope.selectedTenure = tenure;
        };

        $scope.getItemTotal = function (cartItem) {
            return Cart.getItemTotal(cartItem);
        };

        $scope.getAddonTotal = function (addons) {
            var total = 0;
            angular.forEach(addons, function (item, index) {
                if (item.cost && item.name !== 'ssl') {
                    total += item.cost * $scope.selectedTenure;
                }
            }, total);

            return total;
        };

        $scope.getGrandTotal = function () {
            return Cart.getGrandTotal();
        };

        $scope.clearCartAndGoBack = function () {
            Cart.emptyCart();
            $location.path('/renewal');
        };

        $scope.getDescription = function (cartItem) {
            var productName;

            if (cartItem.productCategory == 'domains') {
                productName = cartItem.domainName
            } else {
                productName = cartItem.productName;
            }

            if (cartItem.transactionType === 'renew') {
                return "Renewal of " + productName  + " till " + $scope.getEndDate(cartItem, cartItem.selectedTenure);
            } else {
                return "Addition of " + productName + " till " + $scope.getEndDate(cartItem, cartItem.selectedTenure);
            }
        };

        $scope.getEndDate = function (cartItem, tenure) {
            var endDate;
            var expireDate = new Date(cartItem.expiresOn);
            endDate = expireDate.setMonth(expireDate.getMonth() + parseInt(tenure));
            return moment(endDate).format('Do MMMM YYYY');
        };

        $scope.getColumns = function () {
            return Math.floor(12 / $scope.paymentMethods.length);
        };

        $scope.checkout = function () {
            if ($scope.shoppingCart.items.length === 0) {
                AlertService.showAlert('error', 'You have no items in cart to checkout!', TIMEOUT);
                return false;
            }
            $scope.checkoutBtnTxt = "Processing...";
            Cart.checkout($scope.selectedMethod);
        }
    });