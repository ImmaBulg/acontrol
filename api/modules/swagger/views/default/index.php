<?php

use common\helpers\Html;

$this->title = Yii::t('api.app', 'Swagger');
?>
<div id="swagger-ui-container" class="swagger-ui-wrap"></div>
<?php
$script = <<< JS
jQuery(function () {
	var url = window.location.search.match(/url=([^&]+)/);

	if (url && url.length > 1) {
		url = decodeURIComponent(url[1]);
	} else {
		url = "$jsonPath";
	}

	window.swaggerUi = new SwaggerUi({
		url: url,
		dom_id: "swagger-ui-container",
		supportedSubmitMethods: ['get', 'post', 'put', 'delete', 'patch'],
		onComplete: function(swaggerApi, swaggerUi) {
			$('pre code').each(function(i, e) {
				hljs.highlightBlock(e)
			});
		},
		onFailure: function(data) {
			log("Unable to Load SwaggerUI");
		},
		docExpansion: "none",
		sorter : "alpha",
		validatorUrl: null
	});

	function addApiKeyAuthorization() {
		var key = $('#input_apiKey')[0].value;
		log("key: " + key);
		
		if(key && key.trim() != "") {
			log("added key " + key);
			window.authorizations.add("api_key", new ApiKeyAuthorization("api_key", key, "query"));
		}
	}

	$('#input_apiKey').change(function() {
		addApiKeyAuthorization();
	});
	window.swaggerUi.load();
});
JS;
$this->registerJs($script); ?>