App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/screenshotsgenerator/backoffice_view", {
        controller: 'ScreenshotsgeneratorViewController',
        templateUrl: BASE_URL+"/screenshotsgenerator/backoffice_view/template"
    });

}).controller("ScreenshotsgeneratorViewController", function($scope, $window, Header, Screenshotsgenerator) {

    $scope.header = new Header();
    $scope.header.button.left.is_visible = false;
    $scope.header.loader_is_visible = false;
    $scope.content_loader_is_visible = true;

    Screenshotsgenerator.loadData().success(function(data) {
        $scope.header.title = data.title;
        $scope.header.icon = data.icon;
    }).finally(function() {
        $scope.content_loader_is_visible = false;
        //$scope.$apply();


    });
    
    $scope.download = function(id,url_key,app_store_icon,google_play_icon,domain){
    	$scope.content_loader_is_visible = true;

    	Screenshotsgenerator.checkCredits(url_key,id).success(function(data) {
        	$scope.content_loader_is_visible = false;
	        var confirmed = false;
			if(data.success==-1){
				alert("Sorry you don't have enough credits.Please buy from Siberian Marketplace or contact support@h5.gs");
				return false;
			}
			
	        if(data.success==0){
	         	confirmed = confirm(data.message+', Continue ?');
	        }
    		if (confirmed || data.success==1 ){
    			window.open('http://store.pepoapp.com/screenshots/gen.php?domain='+domain+'&appkey='+url_key+'&google_play_icon='+google_play_icon+'&app_store_icon='+app_store_icon+'&app_id='+id+'&token='+data.token);
    		
    		}
	    }).finally(function() {
	        $scope.content_loader_is_visible = false;
	        //$scope.$apply();
	
	
	    });
    	
    }
    
    $scope.$on('pagination:loadPage', function (event, status, config) {
		$scope.perPage = 10;	

	});
    
    

});