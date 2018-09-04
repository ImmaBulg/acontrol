"use strict";

window.onload = function () {

    var areaChartInterval;

    function initAreaChartInterval() {
        areaChartInterval = setInterval(function() {
            jQuery.getJSON("http://clients.silver-gate.co.il/dashboard/metmon", function (data) {
                console.log(data);
                // Metmon area
                var tempArea = jQuery("#metmon-area-temp").highcharts();
                var cubicArea = jQuery("#metmon-area-cubic_meter").highcharts();
                var cubicHourArea = jQuery("#metmon-area-cubic_meter_hour").highcharts();
                var kilowattArea = jQuery("#metmon-area-kilowatt").highcharts();
                var kilowattHourArea = jQuery("#metmon-area-kilowatt_hour").highcharts();

                if (tempArea) {
                    if (data["incoming_temp"] && data["incoming_temp"].date) {
                        let date = data["incoming_temp"].date.split(" ")[0];
                        let time = data["incoming_temp"].date.split(" ")[1];
                        var ITAreaX = (new Date(Date.UTC(parseInt(date.split("-")[0]), parseInt(date.split("-")[1]) - 1, parseInt(date.split("-")[2]), time.split(":")[0], time.split(":")[1], time.split(":")[2]))).getTime();
                        console.log(ITAreaX);
                        var ITAreaY = parseFloat(data["incoming_temp"].value);
                        tempArea.series[0].addPoint([ITAreaX, ITAreaY]);
                        var OTAreaX = (new Date(Date.UTC(parseInt(date.split("-")[0]), parseInt(date.split("-")[1]) - 1, parseInt(date.split("-")[2]), time.split(":")[0], time.split(":")[1], time.split(":")[2]))).getTime();
                        var OTAreaY = parseFloat(data["outgoing_temp"].value);
                        tempArea.series[1].addPoint([OTAreaX, OTAreaY]);
                    }
                    if (data["cubic_meter"] && data["cubic_meter"].date) {
                        let date = data["incoming_temp"].date.split(" ")[0];
                        let time = data["incoming_temp"].date.split(" ")[1];
                        var CMAreaX = (new Date(Date.UTC(parseInt(date.split("-")[0]), parseInt(date.split("-")[1]) - 1, parseInt(date.split("-")[2]), time.split(":")[0], time.split(":")[1], time.split(":")[2]))).getTime();
                        var CMAreaY = parseFloat(data["cubic_meter"].value);
                        cubicArea.series[0].addPoint([CMAreaX, CMAreaY]);
                    }
                    if (data["cubic_meter_hour"] && data["cubic_meter_hour"].date) {
                        let date = data["incoming_temp"].date.split(" ")[0];
                        let time = data["incoming_temp"].date.split(" ")[1];
                        var CMHAreaX = (new Date(Date.UTC(parseInt(date.split("-")[0]), parseInt(date.split("-")[1]) - 1, parseInt(date.split("-")[2]), time.split(":")[0], time.split(":")[1], time.split(":")[2]))).getTime();
                        var CMHAreaY = parseFloat(data["cubic_meter_hour"].value);
                        cubicHourArea.series[0].addPoint([CMHAreaX, CMHAreaY]);
                        $(".cubic_meter_hour").text(CMHAreaY + " m³/h");
                    }
                    if (data["kilowatt"] && data["kilowatt"].date) {
                        let date = data["incoming_temp"].date.split(" ")[0];
                        let time = data["incoming_temp"].date.split(" ")[1];
                        var KAreaX = (new Date(Date.UTC(parseInt(date.split("-")[0]), parseInt(date.split("-")[1]) - 1, parseInt(date.split("-")[2]), time.split(":")[0], time.split(":")[1], time.split(":")[2]))).getTime();
                        var KAreaY = parseFloat(data["kilowatt"].value);
                        kilowattArea.series[0].addPoint([KAreaX, KAreaY]);
                    }
                    if (data["kilowatt_hour"] && data["kilowatt_hour"].date) {
                        let date = data["incoming_temp"].date.split(" ")[0];
                        let time = data["incoming_temp"].date.split(" ")[1];
                        var KHMAreaX = (new Date(Date.UTC(parseInt(date.split("-")[0]), parseInt(date.split("-")[1]) - 1, parseInt(date.split("-")[2]), time.split(":")[0], time.split(":")[1], time.split(":")[2]))).getTime();
                        var KHAreaY = parseFloat(data["kilowatt_hour"].value);
                        kilowattHourArea.series[0].addPoint([KHMAreaX, KHAreaY]);
                        $(".kilowatt_hour").text(KHAreaY + " Kwh");

                    }
                }
            });
        }, 60000);
    }

    if (jQuery("#realtime-enabled").length) {
        initAreaChartInterval();
    }

    jQuery(document).on("pjax:send", function() {
        clearInterval(areaChartInterval);
        jQuery("body").append("<div id=\"report-overlay\"></div>");
        jQuery("body").append("<div id=\"report-spinner-holder\">' .Yii::t('frontend.view', 'Loading'). '<div id=\"report-spinner\"><div class=\"rect rect1\"></div><div class=\"rect rect2\"></div><div class=\"rect rect3\"></div><div class=\"rect rect4\"></div><div class=\"rect rect5\"></div></div></div>");
    });
    jQuery(document).on("pjax:complete", function() {

        if (jQuery("#realtime-enabled").length) {
            initAreaChartInterval();
        }

        jQuery("#report-overlay").remove();
        jQuery("#report-spinner-holder").remove();
    });
};