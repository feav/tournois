/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
MatchModule.config(['$routeProvider', function($routeProvider){
    ROUTE_MATCH.forEach(function(item,index){
        $routeProvider
            .when(item.link, {
                templateUrl:item.templateUrl,
                controller:item.controller,
                name:item.name
        })
    });
    $routeProvider.otherwise({redirectTo:ROUTE_MATCH[0].link});
}]);
