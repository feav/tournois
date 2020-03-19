
MatchModule
.controller("Match", ['$scope','MatchService', '$routeParams','$location', function ($scope, MatchService, $routeParams, $location) {

    $scope.getMatchCours = function () {
        var promiseGetMatchCours = MatchService.getMatchCours();
        promiseGetMatchCours.then(
            function (data) {
                if(data.tournoi.etat == "termine"){
                    $('.pop-end-tournoi').css('display','block');
                    clearInterval(intervalId1);
                    //return false;
                }
                if( (data.matchs.length == 1) && $('.controller').data('page') == "index"){
                    window.location.href = $('body').data('base-url')+"finale-tournoi/"+$('body').data('tournoi-id');
                    return false;
                }
                if( $('.controller').data('page') == "score"){
                    $scope.getMatchTermine();
                }
                $scope.matchs = data.matchs;
                $scope.tournoi = data.tournoi;
            },
            function (data) {
                console.log(data);
            }
        );
    };

    $scope.getMatchAttente = function () {
        var promiseGetMatchAttente = MatchService.getMatchAttente();
        promiseGetMatchAttente.then(
            function (data) {
                if(data.tournoi.etat == "termine"){
                    $('.pop-end-tournoi').css('display','block');
                    clearInterval(intervalId1);
                    //return false;
                }
                $scope.matchs = data.matchs;
                $scope.tournoi = data.tournoi;
            },
            function (data) {
                console.log(data);
            }
        );
    };

    if($('.controller').data('page') != "match_attente"){
        intervalId1 = setInterval($scope.getMatchCours, 15000);
        $scope.getMatchCours();
    }
    else{
        intervalId1 = setInterval($scope.getMatchAttente, 15000);
        $scope.getMatchAttente();
    }

    $scope.getMatchTermine = function () {
        var promiseGetMatchTermine= MatchService.getMatchTermine();
        promiseGetMatchTermine.then(
            function (data) {
                $scope.matchsTermine = data;
                console.log(data);
            },
            function (data) {
                console.log(data);
            }
        );
    };
    
    Object.size = function(obj) {
        var size = 0, key;
        for (key in obj) {
            if (obj.hasOwnProperty(key)) size++;
        }
        return size;
    };

}])

