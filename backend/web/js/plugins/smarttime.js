Date.prototype.getFullMinutes = function () {
    if (this.getMinutes() < 10) {
        return '0' + this.getMinutes();
    }
    return this.getMinutes();
};

Date.prototype.getFullSeconds = function () {
    if (this.getSeconds() < 10) {
        return '0' + this.getSeconds();
    }
    return this.getSeconds();
};

Date.prototype.getFullHours = function () {
    if (this.getHours() < 10) {
        return '0' + this.getHours();
    }
    return this.getHours();
};

angular.module('smartTime.service', [])
    .factory('padString', function() {
        return function(input, length, padding, padRight) {
            padRight = angular.isDefined(padRight) ? !!padRight : false;
            var result = input + '',
                i=0;
            if (result.length < length) {
                for (i=0; i<(length - (input+'').length); i++) {
                    if (padRight) {
                        result += padding + '';
                    } else {
                        result = padding + result + '';
                    }
                }
            }
            return result;
        }
    });
angular.module('smartTime.directive', [])
    .directive('smartTime', ['padString', '$timeout', function(padString, $timeout) {
        return {
            restrict: 'EA',
            scope: {
                value: '=smtValue',
                required: '=?smtRequired',
                name: '=?smtName',
                form: '=?smtForm'
            },

            link: function(scope, element, attrs) {
                scope.required = (angular.isDefined(scope.required) ? scope.required : false);
                scope.name = (angular.isDefined(scope.name) ? scope.name : '');
                scope.form = (angular.isDefined(scope.form) ? scope.form : null);

                var ptrn24 = /([01]\d|2[0-3]):([0-5]\d)/;
                var match = null;
                var timerPromise = null;
                var suggestionInterval = 15;

                scope.currentIndex = 0;

                scope.data = {
                    raw: '',
                    show: false
                };

                if (angular.isDate(scope.value)) {
                    scope.data.raw = scope.value.getFullHours() + ':' + scope.value.getFullMinutes();
                } else {
                    scope.value = new Date(1970, 0, 1, 0,0,0);
                }

                scope.suggestions = [];

                scope.blur = function() {
                    timerPromise = $timeout(function() {
                        scope.data.show = false;

                        // TODO Check if the input can be interpreted as a valid time string
                        /*if ( (scope.data.raw.length == 3 || scope.data.raw.length == 4) && scope.data.raw.indexOf(':') < 0) {

                        }*/

                        updateValue();
                    }, 200);
                };

                scope.focus = function() {
                    showSuggestions();
                };

                scope.change = function() {
                    showSuggestions();
                };

                scope.keyPress = function(event) {
                    var key = event.which || event.keyCode;
                    if (key == 38) { // UP
                        event.preventDefault();
                        scope.currentIndex--;
                        scope.currentIndex = (scope.currentIndex < 0 ? scope.suggestions.length-1 : scope.currentIndex)
                            % scope.suggestions.length;
                    } else if (key == 40) { // DOWN
                        event.preventDefault();
                        scope.currentIndex++;
                        scope.currentIndex = scope.currentIndex % scope.suggestions.length;
                    } else if (key == 13 || key == 9) { // ENTER or TAB
                        if (typeof scope.suggestions[scope.currentIndex] !== 'undefined') {
                            scope.data.raw = scope.suggestions[scope.currentIndex].val;
                            updateValue();
                        }
                        scope.data.show = false;
                    }
                };

                scope.select = function(index) {
                    if (typeof scope.suggestions[index] !== 'undefined') {
                        scope.data.raw = scope.suggestions[index].val;
                    }
                    scope.data.show = false;
                };

                element.on('$destroy', function() {
                    $timeout.cancel(timerPromise);
                });

                function showSuggestions() {
                    scope.suggestions = []; // empty suggestions
                    scope.currentIndex = 0;

                    if (typeof scope.data.raw !== 'undefined') {
                        match = scope.data.raw.match(ptrn24);
                        var timeHelper = new Date();

                        if (scope.data.raw.length > 0 && match == null) {
                            var dataSplit = scope.data.raw.split(':');

                            // More than 2 characters/number and no colon
                            if (scope.data.raw.indexOf(':') < 0) {
                                if (scope.data.raw.length == 4) {
                                    dataSplit = [
                                        scope.data.raw.slice(0,2),
                                        scope.data.raw.slice(2)
                                    ];
                                } else if (scope.data.raw.length == 3) {
                                    dataSplit = [
                                        scope.data.raw.slice(0,1),
                                        scope.data.raw.slice(1)
                                    ];
                                }
                            }
                            var dataHInt = parseInt(dataSplit[0]);
                            var dataMInt = (typeof dataSplit[1] !== 'undefined' && dataSplit[1].length > 0 ? parseInt(dataSplit[1]) : 0);
                            var hasMins = (typeof dataSplit[1] !== 'undefined' && dataSplit[1].length > 0);
                            var i = 0,
                                minutesHelper = 0,
                                hasSuggestion = false;

                            if (!isNaN(dataHInt) && !isNaN(dataMInt)
                                && dataHInt >= 0 && dataHInt <= 24
                                && dataMInt >= 0 && dataMInt < 60) {

                                if ( [1,3,4,5].indexOf(dataHInt) > -1 && dataSplit[0].length==1) {
                                    timeHelper.setHours(dataHInt + 12, 0);
                                } else if (dataHInt == 2 && dataSplit[0].length==1) {
                                    timeHelper.setHours(dataHInt + 18, 0);
                                } else if ( [6,7,8,9].indexOf(dataHInt) > -1 && dataSplit[0].length==1) {
                                    timeHelper.setHours(dataHInt + 12, 0);
                                } else {
                                    timeHelper.setHours(dataHInt, 0);
                                }
                                for (i=0; i<10; i++) {
                                    if (hasMins) {
                                        if (dataMInt<6) {
                                            minutesHelper = dataMInt*10 + i*suggestionInterval;
                                            if (minutesHelper < dataMInt*10+10) {
                                                timeHelper.setMinutes(minutesHelper);
                                                hasSuggestion = true;
                                            } else {
                                                hasSuggestion = false;
                                            }
                                        }
                                    } else {
                                        timeHelper.setMinutes(i*suggestionInterval);
                                        hasSuggestion = true;
                                    }
                                    if (hasSuggestion) {
                                        scope.suggestions.push({
                                            val: padString( timeHelper.getHours(), 2, '0')
                                                + ':' + padString( timeHelper.getMinutes(), 2, '0'),
                                            selected: false
                                        });
                                    }
                                }
                                if (dataMInt > 9 && dataMInt < 60) {
                                    timeHelper.setMinutes(dataMInt);
                                    console.log(dataMInt, timeHelper);

                                    scope.suggestions.push({
                                        val: padString( timeHelper.getHours(), 2, '0')
                                            + ':' + padString( timeHelper.getMinutes(), 2, '0'),
                                        selected: false
                                    });
                                }
                            }

                        }
                    }

                    if (scope.suggestions.length > 0) {
                        scope.suggestions[0].selected = true;
                        scope.data.show = true;
                    } else {
                        scope.data.show = false;
                    }
                }

                function updateValue() {
                    if (typeof scope.data.raw !== 'undefined') {
                        match = scope.data.raw.match(ptrn24);
                        if (match !== null && typeof match[1] !== 'undefined' && typeof match[2] !== 'undefined') {
                            scope.value.setHours(parseInt(match[1]));
                            scope.value.setMinutes(parseInt(match[2]));
                            setValidity(true);
                        } else {
                            setValidity(false);
                        }
                    }
                }

                function setValidity(value) {
                    if (scope.form !== null && scope.form[scope.name]) {
                        scope.form[scope.name].$setValidity('timeInvalid', !!value)
                    }
                }
            },

            template: '' +
                '<input type="text" name="{{name}}" class="smt-input" ng-blur="blur()" ng-change="change()" ng-model="data.raw" ' +
                    'ng-keydown="keyPress($event)" ng-focus="focus()" ng-trim="" ng-required="required" ' +
                    "ng-model-options=\"{debounce: {'default': 200, 'blur': 0}}\" />" +
                '<div class="smt-suggestions" ng-show="data.show">' +
                    '<ul>' +
                        "<li ng-repeat=\"s in suggestions\" ng-class=\"{selected:currentIndex==$index}\" " +
                            'ng-click="select($index)">{{s.val}}</li>' +
                    '</ul>' +
                '</div>'
        }
    }]);
angular.module('smartTime', ['smartTime.service', 'smartTime.directive']);