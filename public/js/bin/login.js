angular.module('bigrockApp')
    .controller('LoginCtrl', function ($scope, $rootScope, $localStorage, $location, $timeout, AuthService, AlertService, TIMEOUT) {

        $rootScope.title = "BigRock: Renew Your Order";
        $rootScope.flowType = 'renewal';
        
        $scope.loginBtnText = "Login";
        $scope.forgotPassBtn = "Send Instructions";
        $scope.showForgotPass = false;

        $scope.signIn = function (valid) {
            if (!valid) {
                return;
            }
            $scope.loginBtnText = "Logging in...";

            var creds = {
                email: $scope.email,
                password: $scope.password
            };

            AuthService.login(creds).then(function (response) {
                $localStorage.token = response.token;
                $rootScope.currentUser = AuthService.getUser();
                //$timeout(function () {},0, false);
                $location.path('/renewal').replace();

            }).catch(function (response) {
                // Response Error handling for errors outside the global handling scope
                if (response.status !== 401 && response.status !== 403) {
                    var msg = response.message || "Aww Snap! You disturbed the delicate balance of the AuthKeeper.";
                    AlertService.showAlert('error', msg, TIMEOUT)
                }
                $scope.loginBtnText = "Login";
            });
        };

        $scope.forgotPass = function (valid) {
            if (!valid) {
                return;
            }


            AuthService.forgotPass($scope.email).then(function (response) {
                if (response.status === 'success') {
                    $scope.forgotPassBtn = "Email successfully sent!";
                } else {
                    $scope.forgotPassBtn = "Send Instructions";
                }

            }).catch(function (response) {
                // Response Error handling for errors outside the global handling scope
                if (response.status !== 401 && response.status !== 403) {
                    var msg = response.message || "Aww Snap! We were unable to send 'reset password' instruction to the give email. Please try again later.";
                    AlertService.showAlert('error', msg, TIMEOUT)
                }
                $scope.forgotPassBtn = "Send Instructions";
            });
        }

    });