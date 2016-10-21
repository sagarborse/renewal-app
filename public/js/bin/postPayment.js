angular.module('bigrockApp')
    .controller('PostPaymentCtrl', function ($rootScope, $scope, $routeParams, Cart) {

        $rootScope.flowType = 'renewal';
        $scope.status = $routeParams.status;

        // check global var paidCart
        if (typeof paidCart !== 'undefined') {
            $scope.paidCart = paidCart;
        } else {
            $scope.paidCart = { items:[], grandTotal: 0 }
        }

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

        $scope.getNow = function () {
            return moment(new Date).format('Do MMMM YYYY');
        }
    });