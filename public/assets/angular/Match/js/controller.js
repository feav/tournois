
MatchModule
.controller("Match", ['$scope','MatchService', '$routeParams','$location', function ($scope, MatchService, $routeParams, $location) {

    $scope.getMatchCours = function () {

        var promiseGetMatchCours = MatchService.getMatchCours();
        promiseGetMatchCours.then(
            function (data) {
                if(data.tournoi.etat == "termine"){
                    $('.pop-end-tournoi').css('display','block');
                }
                if( (data.matchs.length == 1) && $('.controller').data('page') == "index"){
                    window.location.href = $('body').data('base-url')+"finale-tournoi/"+$('body').data('tournoi-id');
                    return false;
                }
                $scope.matchs = data.matchs;
                $scope.tournoi = data.tournoi;
                console.log(data);
            },
            function (data) {
                console.log(data);
            }
        );

    };
    $scope.getMatchCours();

    Object.size = function(obj) {
        var size = 0, key;
        for (key in obj) {
            if (obj.hasOwnProperty(key)) size++;
        }
        return size;
    };

}])

