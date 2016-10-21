angular.module('bigrockApp')

    .filter('replace', function () {
        return function (input, search, replace) {
            if (input.indexOf(search) > -1) {
                return input.replace(search, replace);
            }
            return input;
        }
    })

    .filter('debug', function () {
        return function (input) {
            if (input === '') return 'empty string';
            return input ? input : ('' + input);
        };
    })

    .filter('isEmpty', function () {
        return function (obj) {
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
    })

    .filter('ifEmpty', function () {
        return function (input, defaultValue) {
            if (angular.isUndefined(input) || input === null || input === '') {
                return defaultValue;
            }

            return input;
        }
    });


