angular.module('bigrockApp')

    .factory('AlertService', function ($rootScope) {
        var AlertService;

        $rootScope.alert = {
            type: null,
            message: null,
            timeout: 500
        };

        return AlertService = {

            showAlert: function (type, msg, timeout) {
                AlertService.clearAlert();
                $rootScope.alert.type = type;
                $rootScope.alert.message = msg;
                $rootScope.alert.timeout = timeout;
            },

            clearAlert: function () {
                $rootScope.alert = {
                    type: null,
                    message: null,
                    timeout: 500
                };
            }

        };
    })

    .factory('Domain', function ($http) {
        return {
            checkAvailability: function (domainsData) {
                return $http({
                    method: 'GET',
                    url: '/api/v1/domains/checkAvailability',
                    params: domainsData,
                    paramSerializer: '$httpParamSerializerJQLike'
                });
            }
        };
    })

    .factory('Products', function ($http, $httpParamSerializerJQLike) {
        return {
            getPricing: function (product) {
                var pricingData = {};

                $http({
                    method: 'GET',
                    url: '/api/v1/products/showPricing',
                    data: $httpParamSerializerJQLike(product)
                }).success(function (data) {
                    pricingData = data;
                });

                return pricingData;
            }
        }
    })

    .factory('Orders', function ($http) {
        return {
            getOrders: function () {
                return $http.get('/api/v1/customer/orders', {cache:true}).then(function (response) {
                    return response.data;
                });
            }
        }
    })

    .factory('AuthService', ['$http', '$localStorage', function ($http, $localStorage, $rootScope) {

        //var tokenClaims = getClaimsFromToken();
        var user = getUserFromToken();

        // --- Private function ---
        function urlBase64Decode(str) {
            var output = str.replace('-', '+').replace('_', '/');
            switch (output.length % 4) {
                case 0:
                    break;
                case 2:
                    output += '==';
                    break;
                case 3:
                    output += '=';
                    break;
                default:
                    throw 'Illegal base64url string!';
            }
            return window.atob(output);
        }

        function getUserFromToken() {
            var token = $localStorage.token;
            var tokenObj = {};
            if (typeof token !== 'undefined' && token !== null) {
                var encoded = token.split('.')[1];
                tokenObj = JSON.parse(urlBase64Decode(encoded));
                return tokenObj.data;
            }
            return null;
        }

        function login(creds) {
            return $http.post('/api/v1/customer/authenticate', creds).then(function (response) {
                return response.data;
            });
        }

        function logout(callback) {
            user = {};
            delete $localStorage.token;
            if (typeof callback === 'function') {
                callback();
            }
        }

        function forgotPass(email) {
            return $http.post('/api/v1/customer/forgotPassword', {email: email}).then(function (response) {
                return response.data;
            });
        }

        return {
            logout: logout,
            login: login,
            getUser: getUserFromToken,
            forgotPass: forgotPass
        }
    }])

    .factory('CartItem', function () {

        // Constructor
        var CartItem = function (product) {

            this.orderId = product.orderId || null;
            this.productName = product.productName;
            this.productCategory = product.productCategory;
            this.productType = product.productType;
            this.planId = product.planId;
            this.location = product.location;
            this.domainName = product.domainName;
            this.selectedTenure = product.selectedTenure || 12;
            this.description = product.description || '';

            if (isset(product.productKey)) {
                this.productKey = product.productKey;
            }

            if (isset(product.addons)) {
                this.addons = product.addons;
            }

            if (isset(product.renew)) {
                this.transactionType = 'renew';
            } else if (isset(product.add)) {
                this.transactionType = 'add';
            } else if (isset(product.transactionType)) {
                this.transactionType = product.transactionType;
            }

            if (isset(product.add)) {
                this.pricing = product.add;
            } else if (isset(product.renew)) {
                this.pricing = product.renew;
            } else if (isset(product.pricing)) {
                this.pricing = product.pricing;
            }

            this.expiresOn = product.expiresOn;
            this.createdOn = product.createdOn;
        };


        return (CartItem);
    })

    .factory('Cart', function ($localStorage, $http, CartItem, $log) {

        var cartObj = $localStorage.shoppingCart || {items: []};

        // prototype methods
        function addItem(product) {
            var cartItem = new CartItem(product);
            var exists = false;
            if (cartItem.transactionType === 'renew') {
                angular.forEach(cartObj.items, function (item, index) {
                    if (isset(item.orderId) && item.orderId === cartItem.orderId) {
                        exists = true;
                    }
                });
            }
            if (exists === false) {
                cartObj.items.push(cartItem);
            } else {
                $log.warn('Item already exists in cart.');
            }
        }

        function removeItem(index) {
            cartObj.items.splice(index, 1);
        }

        function getItemTotal(cartItem) {
            if (cartItem.productCategory === 'domains') {
                var selectedTenureInYears = cartItem.selectedTenure / 12;
                return selectedTenureInYears * cartItem.pricing[cartItem.selectedTenure];
            }
            return cartItem.selectedTenure * cartItem.pricing[cartItem.selectedTenure];
        }

        function emptyCart() {
            cartObj.items = [];
            $localStorage.shoppingCart = {items: []};
        }

        function getGrandTotal() {
            var total = 0;
            angular.forEach(cartObj.items, function (item) {
                if (item.addons) {
                    angular.forEach(item.addons, function (addon) {
                        if (addon.cost && addon.name !== 'ssl') {
                            total += (addon.cost * item.selectedTenure);
                        }
                    });
                }
                total += getItemTotal(item);
            });

            return total;
        }

        function getPaymentMethods() {
            return $http.get('/api/v1/customer/paymentMethods', {cache:true}).then(function (response) {
                return response.data;
            });
        }

        function addFormFields(form, data) {
            if (data != null) {
                angular.forEach(data, function (name, value) {
                    if (value != null) {
                        var input = $("<input/>").attr("type", "hidden").attr("name", name).val(value);
                        form.append(input);
                    }
                });
            }
        }

        function checkout(selectedMethod) {
            var cart = cartObj;
            cart.grandTotal = getGrandTotal();
            var form = $('<form/></form>');
            form.attr("action", "/api/v1/cart/payment");
            form.attr("method", "POST");
            form.attr("style", "display:none;");
            form.append("<input name='token' value='" + $localStorage.token + "' />")
            form.append("<textarea name='paymentMethod'>" + JSON.stringify(selectedMethod) + "</textarea>");
            form.append("<textarea name='cart'>" + JSON.stringify(cart) + "</textarea>");
            form.append("<input name='grandTotal' value='" + getGrandTotal() + "' >")
            $("body").append(form);
            form.submit();
            form.remove();
        }

        return {
            cartObj: cartObj,
            addItem: addItem,
            removeItem: removeItem,
            emptyCart: emptyCart,
            getItemTotal: getItemTotal,
            getGrandTotal: getGrandTotal,
            getPaymentMethods: getPaymentMethods,
            checkout: checkout
        }
    });