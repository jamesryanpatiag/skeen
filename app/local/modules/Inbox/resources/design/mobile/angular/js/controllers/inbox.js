App.config(function($routeProvider) {

    $routeProvider.when(BASE_URL+"/inbox/mobile_list/index/value_id/:value_id", {
        controller: 'InboxListController',
        templateUrl: BASE_URL+"/inbox/mobile_list/template",
        code: "inbox"
    }).when(BASE_URL+"/inbox/mobile_view/index/message_id/:message_id/value_id/:value_id", {
        controller: 'InboxViewController',
        templateUrl: BASE_URL+"/inbox/mobile_view/template",
        code: "inbox-view cms"
    }).when(BASE_URL+"/inbox/mobile/comment_view/index/message_id/:message_id/value_id/:value_id", {
        controller: 'InboxCommentViewController',
        templateUrl: BASE_URL+"/inbox/mobile_comment_view/template",
        code: "inbox-comment-view"
    });

}).controller('InboxListController', function($window, $scope, $routeParams, $location, Url, Inbox, Customer) {

    $scope.is_loading = true;
    $scope.value_id = Inbox.value_id = $routeParams.value_id;

    $scope.factory = Inbox;
    $scope.collection = new Array();

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.loadContent = function() {

        if(Customer.isLoggedIn()) {
            $scope.customer_id  = Customer.id;
        } else {
            $scope.customer_id  = null;
        }
        Inbox.findAll($scope.customer_id).success(function (data) {
            $scope.page_title = data.page_title;
            $scope.collection = data.collection;
        }).finally(function() {
            $scope.is_loading = false;
        });
    };

    $scope.loadMore = function(offset) {
        return Inbox.findAll($scope.customer_id, offset).success(function (data) {
            angular.forEach(data.collection, function(elem) {
                $scope.collection.push(elem);
            });
        });
    };

    $scope.showItem = function(item) {
        $location.path(Url.get("inbox/mobile_view/index", {message_id: item.id, value_id: $scope.value_id}));
    };

    $scope.gotToLogin = function() {
        $location.path(Url.get("customer/mobile_account_login/"));
    };

    $scope.loadContent();
}).controller('InboxViewController', function($window, $scope, $routeParams, $location, Url, Inbox, modalManager, ImageGallery) {

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });


    $scope.gallery = ImageGallery;
    $scope.is_loading = true;

    $scope.loadContent = function() {
        Inbox.find($routeParams.message_id).success(function(data) {
            $scope.message = data.item;
            $scope.blocks = data.blocks;
            $scope.page_title = data.page_title;
            $scope.icon_url = data.icon_url;
            $scope.delete_message = data.delete_message;
            $scope.title_delete_message = data.title_delete_message;
            $scope.trash_icon_url = "/template/block/colorize/color/" + $window.colors.background.backgroundColor.replace("#","") + "/path/" + btoa($scope.icon_url + "trash.png");
            $scope.comment_icon_url = "/template/block/colorize/color/" + $window.colors.background.backgroundColor.replace("#","") + "/path/" + btoa($scope.icon_url + "reply.png");

        }).finally(function() {
            $scope.is_loading = false;
        });
    };

    $scope.confirmDeleteMessage = function() {
        var modal = {
            "title": $scope.title_delete_message,
            "content": $scope.delete_message,
            "show_cancel": true,
            "ok_label": "OK"
        };

        modal.confirmAction = function() {
            $scope.deleteMessage();
        };

        modalManager.addToQueue(modal);
        modalManager.show();
    };

    $scope.deleteMessage = function() {
        Inbox.deleteRootMessage($scope.message.message_id).success(function(data) {
            $window.history.back();
        });
    };

    $scope.viewComments = function() {
        $location.path(Url.get("/inbox/mobile/comment_view/index", {message_id: $scope.message.message_id, value_id: $routeParams.value_id}));
    };

    $scope.loadContent();
}).controller('InboxCommentViewController', function($window, $scope, $routeParams, $location, Url, Inbox, Customer) {

    $scope.is_loading = true;
    $scope.show_post = false;
    $scope.show_bottom_bar = true;
    $scope.reply = {};

    $scope.factory = Inbox;
    $scope.collection = new Array();

    $scope.$on("connectionStateChange", function(event, args) {
        if(args.isOnline == true) {
            $scope.loadContent();
        }
    });

    $scope.loadContent = function() {

        Inbox.findComments($routeParams.message_id, Customer.id).success(function(data) {

            $scope.page_title = data.page_title;
            $scope.icon_url = data.icon_url;
            $scope.parent_is_deleted = data.parent_is_deleted;
            $scope.show_bottom_bar = !$scope.parent_is_deleted;
            $scope.pencil_icon_url = "/template/block/colorize/color/" + $window.colors.background.backgroundColor.replace("#","") + "/path/" + btoa($scope.icon_url + "pencil.png");

            $scope.collection = data.collection;

        }).finally(function() {
            $scope.is_loading = false;
        });

    };

    $scope.showPost = function() {
        $scope.show_post = !$scope.show_post;
        $scope.show_bottom_bar = !$scope.show_bottom_bar;
    };

    $scope.postComment = function() {

        if($scope.reply.message) {

            var data_to_send = {
                "message_id": $routeParams.message_id,
                "message": "<p>" + $scope.reply.message + "</p>",
                "customer_id": Customer.id
            };

            Inbox.postComment(data_to_send, Customer.id).success(function(data) {
                $window.history.back();
            });

        }
    };

    $scope.loadMore = function(offset) {
        return Inbox.findComments($routeParams.message_id, Customer.id, offset).success(function (data) {
            angular.forEach(data.collection, function(elem) {
                $scope.collection.push(elem);
            });
        });
    };

    $scope.loadContent();

});