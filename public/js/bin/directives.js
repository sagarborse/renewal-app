angular.module('bigrockApp')
    .directive('goBack', function () {
        return {
            restrict: 'A',
            link: function (scope, element, attr) {
                element.on('click', function () {
                    history.back();
                    scope.$apply();
                })
            }
        }
    })

    .directive('autofill', function ($timeout) {
        return {
            require: 'ngModel',
            link: function (scope, element, attr, ngModel) {
                var origVal = element.val();
                $timeout(function () {
                    var newVal = element.val();
                    if (ngModel.$pristine && origVal !== newVal) {
                        ngModel.$setViewValue(newVal);
                    }
                }, 500);
            }
        }
    });