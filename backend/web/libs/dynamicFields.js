function DynamicFields(categoryInputId, fieldsUrl, fieldsContainer, id, formId, categoryHiddenInput, categoryPostParam) {
    this._fieldsUrl = fieldsUrl;
    this._fieldsContainer = fieldsContainer;
    this._id = id;
    this._formId = formId;
    this._category = $(categoryInputId).prop('checked') ? 1 : 0;
    this._categoryHiddenInput = categoryHiddenInput;
    this._categoryPostParam = categoryPostParam;

    console.log("Category : %s", this._category);
    var self = this;
    $(document).ready(function () {
        self.getFields();
    });
    $(document).on('change', categoryInputId, function () {
//        old_type = type;
        self._category = $(this).prop('checked') ? 1 : 0;
        self.getFields();
    });
}
function mergeObjects(obj1, obj2) {
    var obj3 = {};
    for (var attribute in obj1) {
        if (obj1.hasOwnProperty(attribute))
            obj3[attribute] = obj1[attribute];
    }
    for (attribute in obj2) {
        if (obj2.hasOwnProperty(attribute))
            obj3[attribute] = obj2[attribute];
    }
    return obj3;
}
DynamicFields.prototype.getFields = function (url, fieldsContainer, isReplace, additionalValues, formId, categoryHiddenInput, category) {
    var data = this.prepareData(additionalValues, formId, categoryHiddenInput, category);
    this.send(url, data, fieldsContainer, isReplace);
};


DynamicFields.prototype.onSuccess = function (fieldsContainer, isReplace) {
    fieldsContainer = typeof fieldsContainer === 'undefined' ? this._fieldsContainer : fieldsContainer;
    isReplace = typeof isReplace === 'undefined' ? false : isReplace;
    var self = this;
    return function (response) {
        console.log('Self %o', self);
        if (!isReplace)
            $(fieldsContainer).html(response.html);
        else $(fieldsContainer).replaceWith(response.html);
        self.announceFields(response);
    }
};

DynamicFields.prototype.announceFields = function (data) {
    var self = this;
    for (var key in data.fields) {
        if (data.fields.hasOwnProperty(key)) {
            $(self._formId).yiiActiveForm('add', {
                'id': data.fields[key]['id'],
                enableAjaxValidation: true,
                validateOnChange: false,
                validateOnBlur: false,
                'name': data.fields[key]['name'],
                'container': data.fields[key]['container'],
                'input': data.fields[key]['input'],
                'error': data.fields[key]['error']
            });
        }

    }
};
DynamicFields.prototype.send = function (url, data, fieldsContainer, isReplace) {
    url = typeof url === 'undefined' ? this._fieldsUrl : url;
    $.ajax({
        url: url,
        type: "POST",
        dataType: "json",
        data: data,
        success: this.onSuccess(fieldsContainer, isReplace)
    });
};

DynamicFields.prototype.prepareData = function (additionalValues, formId, categoryHiddenInput, category) {
    additionalValues = typeof additionalValues === 'undefined' ? [] : additionalValues;
    additionalValues.push({name: this._categoryPostParam, value: this._category});
    formId = typeof formId === 'undefined' ? this._formId : formId;
    categoryHiddenInput = typeof categoryHiddenInput === 'undefined' ? this._categoryHiddenInput : categoryHiddenInput;
    category = typeof category === 'undefined' ? this._category : category;
    var data = $(formId).serializeArray();
    if (typeof additionalValues !== 'undefined') {
        additionalValues.forEach(function (value) {
            data.push(value);
        });
    }
    data = $.param(data);
    $(categoryHiddenInput).prop('value', category);
    return data;
};



