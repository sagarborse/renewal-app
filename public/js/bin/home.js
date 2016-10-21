angular.module('bigrockApp')
    .controller('HomeCtrl', function ($scope, $rootScope, $http, $filter, Domain, Products) {

        $rootScope.pageClass = 'home';

        $scope.domainPageLoading = true;
        $scope.domains = {};
        $scope.domains.defaultTlds = ['com', 'net'];
        $scope.domains.showCount = 5;
        $scope.domains.premiumAfter = 4;
        $scope.domains.premiumAfterCounter = 0;
        $scope.domains.formData = {};
        $scope.domains.domainsArr = [];
        $scope.domains.primaryCollection = {};
        $scope.domains.suggestionsCollection = [];
        $scope.domains.premiumCollection = [];
        $scope.domains.secondaryCollection = [];

        $scope.products = {};
        $scope.products.pricing = Products.getPricing();

        $scope.init = function () {
            setTimeout(function () {
                $scope.domainPageLoading = false;
                $scope.$apply();
            }, 700);
        };

        $scope.checkAvailability = function () {

            $scope.domains.primaryCollection = {};
            $scope.domains.secondaryCollection = [];
            $scope.domains.suggestionsCollection = [];
            $scope.domains.premiumCollection = [];

            $scope.btnLoading = true;

            $scope.domains.formData.domainNames = $filter('lowercase')($scope.domains.formData.domainNames);
            if ($scope.domains.formData.domainNames.indexOf(',') > -1) {
                $scope.domains.domainsArr = $scope.domains.formData.domainNames.split(',')
            } else {
                $scope.domains.domainsArr = [$scope.domains.formData.domainNames];
            }

            angular.forEach($scope.domains.domainsArr, function (domain, key) {
                var expanded, domainsData;
                if (domain.indexOf('.') > -1) {
                    expanded = domain.split(/\.(.+)?/);
                    domainsData = {domainKeyword: expanded[0], tld: expanded[1]};
                } else {
                    domainsData = {domainKeyword: domain, tld: 'com'}
                }

                domain = domainsData.domainKeyword + '.' + domainsData.tld;

                this.primaryCollection[domain] = {
                    domainKeyword: domainsData.domainKeyword,
                    tld: domainsData.tld,
                    domainName: domain,
                    status: 'loading',
                    inCart: false
                };

                Domain.checkAvailability(domainsData).then(function (data) {
                    data = data.data;
                    $scope.domains.primaryCollection[domain]['status'] = data['primary'][domain]['status'];
                    $scope.domains.primaryCollection[domain]['pricing'] = data['primary'][domain]['pricing'];

                    if (data['suggestions'].length > 0) {
                        $scope.domains.suggestionsCollection = data['suggestions'];
                    }

                    if (data['premium'].length > 0) {
                        $scope.domains.premiumCollection = data['premium'];
                    }
                }).then(function () {
                    $scope.domains.secondaryCollection = $scope.buildSecondaryCollection();
                    $scope.btnLoading = false;
                });

            }, $scope.domains)

        };

        $scope.buildSecondaryCollection = function () {
            var collection;

            if ($scope.domains.suggestionsCollection.length === 0 && $scope.domains.premiumCollection.length === 0) {
                collection = [];
            } else if ($scope.domains.suggestionsCollection.length === 0 && $scope.domains.premiumCollection.length > 0) {
                collection = $scope.domains.premiumCollection;
            } else if ($scope.domains.suggestionsCollection.length > 0) {
                var premiumIndex = 0;
                collection = $scope.domains.suggestionsCollection;
                if ($scope.domains.premiumCollection.length > 0) {
                    collection.map(function (value, index) {
                        if (this.domains.premiumAfter === this.domains.premiumAfterCounter && isset(this.domains.premiumCollection[premiumIndex])) {
                            this.domains.premiumAfterCounter = 0;
                            collection.splice(index, 0, this.domains.premiumCollection[premiumIndex]);
                            premiumIndex++;
                        }
                        this.domains.premiumAfterCounter++;
                    }, $scope);
                }
            }

            return collection;
        };

        $scope.getBtnText = function (domainObj) {
            if (domainObj.status === 'available' || domainObj.status === 'premium') {
                if (domainObj.inCart === false) {
                    return 'Add';
                } else {
                    return 'Remove';
                }
            } else if (domainObj.status === 'loading') {
                return 'Loading...'
            } else {
                return 'Disabled';
            }
        };

        $scope.objectLength = function (object) {
            return Object.keys(object).length;
        };

        $scope.beforeAddToCart = function (domain) {
            domain.inCart = !domain.inCart;
        }
    });