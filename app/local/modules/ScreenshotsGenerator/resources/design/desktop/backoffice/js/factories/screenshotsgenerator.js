
App.factory('Screenshotsgenerator', function($http, Url) {

    var factory = {};
	
    factory.loadData = function() {
        return $http({
            method: 'GET',
            url: Url.get("screenshotsgenerator/backoffice_list/load"),
            cache: true,
            responseType:'json'
        });
    };
    
    factory.checkCredits = function(appkey,app_id) {
        return $http({
            method: 'GET',
            url: Url.get("screenshotsgenerator/backoffice_list/checkcredits?app_id="+app_id+"&appkey="+appkey),
            cache: false,
            responseType:'json'
        });
    };

    return factory;
});
