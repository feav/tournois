/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

MatchModule.factory('MatchService', ['$http', '$resource', '$q', function ($http, $resource, $q) {
        var config = {
            headers: {
                'Content-Type': 'application/json'
            }
        };
        return{
            getMatchCours: function () {
                return $http.get(REQUEST_URL_MATCH.findUrl("match_playing", [{name:'id', value:$('body').data('tournoi-id')}]))
                        .then(
                            function (response) {
                                console.log(response.data);
                                return response.data;
                            },
                            function (errResponse) {
                                console.error('Error while listing infos user');
                                return $q.reject(errResponse);
                            });
            }
        };
    }]);
