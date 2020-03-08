
MatchModule
.controller("Match", ['$scope','MatchService', '$routeParams','$location', function ($scope, MatchService, $routeParams, $location) {

    $scope.getMatchCours = function () {
        var promiseGetMatchCours = MatchService.getMatchCours();
        promiseGetMatchCours.then(
            function (data) {
                if(data.tournoi.etat == "termine"){
                    $('.pop-end-tournoi').css('display','block');
                    clearInterval(intervalId1);
                    return false;
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
                console.log(data);
            },
            function (data) {
                console.log(data);
            }
        );
    };
    $scope.getMatchCours();

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

    intervalId1 = setInterval($scope.getMatchCours, 15000);
}])

