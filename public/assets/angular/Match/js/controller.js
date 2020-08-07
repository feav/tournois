/*
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
                if( (data.tournoi.demieFinale_finale == "finale") && $('.controller').data('page') == "index"){
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
*/


MatchModule
.controller("Match", ['$scope','MatchService', '$routeParams','$location', function ($scope, MatchService, $routeParams, $location) {

    $scope.getAllMatch = function () {
        var promiseGetAllMatch = MatchService.getAllMatch();
        promiseGetAllMatch.then(
            function (data) {
                if(data.data_tournoi.etat == "termine"){
                    $('.pop-end-tournoi').css('display','block');
                    clearInterval(intervalSocket);
                    //return false;
                }

                $scope.tournoi = data.data_tournoi;
                $scope.tournoiEncour = data.datas_encour.tournoi;
                $scope.matchsEncour = data.datas_encour.matchs;

                $scope.tournoiEnattente = data.datas_enattente.tournoi;
                $scope.matchsEnattente = data.datas_enattente.matchs;

                $scope.matchsTermine = data.datas_termine;
                if( ($scope.tournoi.demieFinale_finale == "finale") || $scope.tournoi.etat == "termine"){
                   $('.screen-item .screen-finale').css('display', 'block');
                   $('.screen-item .screen-jeux').css('display', 'none');
                }
                else if($scope.tournoi.etat == "en_cours"){
                    $('.screen-item .screen-jeux').css('display', 'block');
                    $('.screen-item .screen-finale').css('display', 'none');
                }
            },
            function (data) {
                console.log(data);
            }
        );
    };
    //intervalSocket = setInterval($scope.getAllMatch, 15000);
    intervalSocket = setInterval($scope.getAllMatch, 15000);
    $scope.getAllMatch();



    $scope.getMatchCours = function () {
        var promiseGetMatchCours = MatchService.getMatchCours();
        promiseGetMatchCours.then(
            function (data) {
                if(data.tournoi.etat == "termine"){
                    $('.pop-end-tournoi').css('display','block');
                    clearInterval(intervalId1);
                    //return false;
                }
                if( (data.tournoi.demieFinale_finale == "finale") && $('.controller').data('page') == "index"){
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
                    clearInterval(intervalId2);
                    //return false;
                }
                $scope.matchsAttente = data.matchs;
                $scope.tournoiAttente = data.tournoi;
            },
            function (data) {
                console.log(data);
            }
        );
    };

    /*intervalId1 = setInterval($scope.getMatchCours, 15000);
    $scope.getMatchCours();

    intervalId2 = setInterval($scope.getMatchAttente, 15000);
    $scope.getMatchAttente();*/
    

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



