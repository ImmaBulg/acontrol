"use strict";

var app = angular.module('irregularHours', ['smartTime']);

app.service('$format_date', function () {
   this.format = function (date) {
       var time = new Date(date);
       return time.getFullHours() + ':' + time.getFullMinutes() + ':' + time.getFullSeconds();
   };
});

app.service('$format_input', function () {
    this.format = function (model_data) {
        if (model_data.length > 0) {
            return this.formatModel(model_data);
        }
        return {};
    };
    this.formatModel = function (model_data) {
        var result = {};
        angular.forEach(model_data, function (data) {
            if (!result[data.day_number]) {
                result[data.day_number] = [];
            }
            data.hours_to = this.getDate(data.hours_to);
            data.hours_from = this.getDate(data.hours_from);
            result[data.day_number].push(data);
        }.bind(this));
        return result;
    };
    this.getDate = function (time) {
        if (time === null) return '';
        var timeValues = time.split(':');
        return new Date(1970, 0, 1, parseInt(timeValues[0]), parseInt(timeValues[1]), 0);
    };
});

app.service('$format_output', ['$format_date', function ($format_date) {
    this.format = function (model_data) {
        var result_data = [];
        angular.forEach(model_data, function(data) {
            angular.forEach(data, function (row) {
                result_data.push({
                    id: row.id,
                    tenant_id: row.tenant_id,
                    day_number: row.day_number,
                    hours_from: $format_date.format(row.hours_from),
                    hours_to: $format_date.format(row.hours_to)
                });
            });
        });
        return result_data;
    }
}]);

app.service('$request_sender', function ($http, $httpParamSerializerJQLike) {
    this.post = function (url, data, callback) {
        return $http({
            url: url,
            method: 'POST',
            data: $httpParamSerializerJQLike(data),
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        }).then(function (response) {
            callback(response.data);
        });
    };
});

app.service('$format_hours', ['$format_date', function ($format_date) {
    this.format = function (model_data) {
        model_data[0].irregular_hours_from = $format_date.format(model_data[0].irregular_hours_from);
        model_data[0].irregular_hours_to = $format_date.format(model_data[0].irregular_hours_to);
        return model_data;
    };
}]);

app.controller('irregularCalendar', function ($scope, $attrs, $format_input, $format_output, $request_sender) {
    var data = JSON.parse($attrs.init);

    $scope.preloader = false;

    $scope.texts = data.language;

    $scope.days_of_week = data.days_of_week;

    $scope.model_data = $format_input.format(data.model_data);

    $scope.addHours = function (day_number) {
        if (!$scope.model_data[day_number]) {
            $scope.model_data[day_number] = [];
        }
        $scope.model_data[day_number].push({
            id: 0,
            tenant_id: data.tenant_id,
            day_number: day_number,
            hours_from: '',
            hours_to: ''
        });
    };

    $scope.deleteHours = function (index, day_number) {
        $scope.model_data[day_number].splice(index, 1);
    };

    $scope.saveIrregular = function () {
        $scope.preloader = true;
        $request_sender.post('/tenant/save-irregular-hours', {tenant_id: data.tenant_id, data: $format_output.format($scope.model_data)}, function (response) {
            $scope.model_data = $format_input.format(response);
            $scope.preloader = false;
            angular.element('#success-modal').modal();
        });
    };
});

app.controller('irregularHour', function($scope, $attrs, $request_sender, $format_input, $format_hours, $window) {
    var data = JSON.parse($attrs.init);
    $scope.preloader = false;
    $scope.texts = data.language;
    $scope.site_options = { "site_irregular_additional_percent": data.site_irregular_additional_percent };
    console.log($scope);
    $scope.model_data = data['model-data'];
    $scope.model_data[0].irregular_additional_percent = data['model-data'][0].irregular_additional_percent;

    $scope.saveHour = function() {
        $scope.preloader = true;
        console.log($scope.model_data[0]);
        $request_sender.post('/tenant/save-irregular-hour', {tenant_id: data.tenant_id, data: $format_hours.format($scope.model_data)}, function(response) {
            console.log(response);
            $scope.model_data[0].irregular_additional_percent = response.irregular_additional_percent;

            $scope.preloader = false;
            angular.element('#success-modal').modal();
        });
    };

    $scope.delHour = function() {
        $scope.preloader = true;
        $request_sender.post('/tenant/del-irregular-hour', {tenant_id: data.tenant_id, data: $scope.model_data}, function(response) {
            $scope.model_data = response;
            $window.location.reload();
            $scope.preloader = false;
            angular.element('#success-modal').modal();
        });
    };
});