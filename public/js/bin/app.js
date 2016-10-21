angular
    .module('bigrockApp', [
        'ngAnimate',
        'ngStorage',
        'ngRoute',
        'ui.bootstrap'
    ])
    .config(['$routeProvider', '$httpProvider', '$locationProvider', 'CartProvider', 'AlertServiceProvider', 'TIMEOUT', function ($routeProvider, $httpProvider, $locationProvider, CartProvider, AlertServiceProvider, TIMEOUT) {

        $httpProvider.interceptors.push(['$q', '$location', '$localStorage', '$rootScope', function ($q, $location, $localStorage, $rootScope) {
            return {
                'request': function (config) {
                    // Add Token to All requests Headers
                    config.headers = config.headers || {};
                    if ($localStorage.token) {
                        config.headers.Authorization = 'Bearer ' + $localStorage.token;
                    }
                    return config;
                },
                'responseError': function (response) {
                    if (response.status === 401 || response.status === 403) {
                        // Delete User
                        $rootScope.currentUser = null;
                        // Response Global Error Handling
                        var message = response.message || response.msg;
                        if (response.status === 403) {
                            message = message || "Session expired. Please login again.";
                        } else if (response.status === 401) {
                            message = message || "Invalid Credentials. Please check your credentials and try again";
                        }
                        AlertServiceProvider.$get().showAlert('error', message, TIMEOUT);
                        // Delete Token
                        $localStorage.token = null;
                        // Empty Cart
                        CartProvider.$get().emptyCart();
                        $location.path('/login');
                    }
                    return $q.reject(response);
                }
            };
        }]);

        $routeProvider
            .when('/login', {
                templateUrl: '/views/partials/login.html',
                controller: 'LoginCtrl',
                controllerAs: 'login',
                authRequired: false
            })
            .when('/renewal', {
                templateUrl: '/views/partials/renewal.html',
                controller: 'RenewalCtrl',
                controllerAs: 'renewal',
                authRequired: true
            })
            .when('/cart', {
                templateUrl: '/views/partials/cart.html',
                controller: 'CartCtrl',
                controllerAs: 'cart',
                authRequired: true
            })
            .when('/cart/:status', {
                templateUrl: '/views/partials/postPayment.html',
                controller: 'PostPaymentCtrl',
                controllerAs: 'postPayment',
                authRequired: true
            })
            .otherwise({
                redirectTo: '/login'
            });

    }])
    .constant('DOMAIN_AVAILABLE', 'Available')
    .constant('DOMAIN_UNAVAILABLE', 'UnAvailable')
    .constant('TIMEOUT', 6000)
    .run(['$rootScope', '$location', '$window', 'AuthService', function ($rootScope, $location, $window, AuthService) {
        $rootScope.$on('$routeChangeStart', function (event, next) {

            // GA page view on route change
            var page;
            if ($window.ga) {
                if ($rootScope.lastAction !== 'logout') {
                    page = 'logout';
                    $rootScope.lastAction = undefined;
                } else {
                    page = $location.path();
                }
                $window.ga('send', 'pageview', { page: page });
            }

            // Check authentication required, if yes (and not authenticated) redirect to Login
            $rootScope.pageClass = next.controllerAs;
            if (next.authRequired === true && !AuthService.getUser()) {
                $location.path('/login');
            }
        })
    }]);


// ----------------
// UTIL FUNCTIONS
// ----------------
function isset(data) {
    return !(data === undefined || data === "" || data === null);
}

function isEmpty(obj) {
    if (typeof obj === 'array') {
        return obj.length() > 0
    } else {
        for (var key in obj) {
            if (obj.hasOwnProperty(key))
                return false;
        }
        return true;
    }
}