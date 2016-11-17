(function (root, factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD. Register as an anonymous module.
        define([], function () {
            return (root.returnExportsGlobal = factory());
        });
    } else if (typeof exports === 'object') {
        // Node. Does not work with strict CommonJS, but
        // only CommonJS-like enviroments that support module.exports,
        // like Node.
        module.exports = factory();
    } else {
        root['Chartist.plugins.ctPointLabels'] = factory();
    }
}(this, function () {

    /**
     * Chartist.js plugin to display a data label on top of the points in a line chart.
     *
     */
    /* global Chartist */
    (function (window, document, Chartist) {
        'use strict';

        var defaultOptions = {
            labelClass: 'ct-label',
            labelOffset: {
                x: 0,
                y: -15
            },
            lineClass: 'ct-label-line',
            lineOffset: {
                x1: 0,
                x2: 0,
                y1: -18,
                y2: -8
            },
            textAnchor: 'middle',
            labelBackground: false,
            labelInterpolationFnc: Chartist.noop
        };

        Chartist.plugins = Chartist.plugins || {};
        Chartist.plugins.ctPointLabels = function (options) {

            options = Chartist.extend({}, defaultOptions, options);

            return function ctPointLabels(chart) {
                if (chart instanceof Chartist.Line) {
                    chart.on('draw', function (data) {
                        if (data.type === 'point') {
                            data.group.elem('text', {
                                x: data.x + options.labelOffset.x,
                                y: data.y + options.labelOffset.y,
                                style: 'text-anchor: ' + options.textAnchor
                            }, options.labelClass).text(options.labelInterpolationFnc(data.value.x === undefined ? data.value.y : data.value.x + ', ' + data.value.y));
                        }
                    });
                }
                if (chart instanceof Chartist.Bar) {
                    chart.on('draw', function (data) {
                        if (data.type === 'bar') {
                            data.group.elem('text', {
                                x: data.x2 + options.labelOffset.x,
                                y: data.y2 + options.labelOffset.y,
                                style: 'text-anchor: ' + options.textAnchor
                            }, options.labelClass).text(options.labelInterpolationFnc(data.value.x === undefined ? data.value.y : data.value.x));

                            if (options.labelBackground == true) {
                                data.group.elem('line', {
                                    x1: data.x2 + options.lineOffset.x1,
                                    x2: data.x2 + options.lineOffset.x2,
                                    y1: data.y2 + options.lineOffset.y1,
                                    y2: data.y2 + options.lineOffset.y1
                                }, options.lineClass, true);
                            }
                        }
                    });
                }
            };
        };

    }(window, document, Chartist));
    return Chartist.plugins.ctPointLabels;

}));