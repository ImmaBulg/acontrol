/**
 * Created with JetBrains PhpStorm.
 * User: DezMonT
 * Date: 09.10.14
 * Time: 11:54
 * To change this template use File | Settings | File Templates.
 */
function initSelect(elem, listUrl, idsUrl, isMultiple, width) {
    var width = width || '50%';
    var additionalInfo = null;
    var options = {
        containerCssClass: 'tpx-select2-container',
        dropdownCssClass: 'tpx-select2-drop',
        cache : true,
        width: width,
        multiple: isMultiple,
        ajax: {
            url: listUrl,
            dataType: 'json',
            data: function (term, page) {
                var data = {
                    value: term,
                    page: page
                };
                if (additionalInfo !== null) {
                    data['additionalInfo'] = additionalInfo
                }
                return data;
            },
            results: function (data, page) {
                var more = (page * data.itemsPerPage) < data.totalCount;
                additionalInfo = data['additionalInfo'];
                return {results: data.results, more: more};
            }

        }
    };
    if(idsUrl !== null) {
        options['initSelection'] = function (element, callback) {
            var id = $(element).attr('value');
            if (id) {
                $.ajax({
                    url: idsUrl,
                    dataType: 'json',
                    data: {
                        id: id
                    }
                }).done(function (data) {
                    callback(data.results)
                });
            }
        }
    }
    return elem.select2(options).on('select2-close',function() {
        additionalInfo = null;
    });
}