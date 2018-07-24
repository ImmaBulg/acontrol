<?php
namespace common\components;

use Yii;

/**
 * UrlManager handles HTTP request parsing and creation of URLs based on a set of rules.
 *
 * UrlManager is configured as an application component in [[\yii\base\Application]] by default.
 * You can access that instance via `Yii::$app->urlManager`.
 *
 * You can modify its configuration by adding an array to your application config under `components`
 * as it is shown in the following example:
 *
 * ~~~
 * 'urlManager' => [
 *     'enablePrettyUrl' => true,
 *     'rules' => [
 *         // your rules go here
 *     ],
 *     // ...
 * ]
 * ~~~
 *
 * @property string $baseUrl The base URL that is used by [[createUrl()]] to prepend to created URLs.
 * @property string $hostInfo The host info (e.g. "http://www.example.com") that is used by
 * [[createAbsoluteUrl()]] to prepend to created URLs.
 * @property string $scriptUrl The entry script URL that is used by [[createUrl()]] to prepend to created
 * URLs.
 */
class UrlManagerStatic extends \yii\web\UrlManager
{
	private $_baseUrl;
	private $_scriptUrl;
	private $_hostInfo;

	/**
	 * Creates an absolute URL using the given route and query parameters.
	 *
	 * This method prepends the URL created by [[createUrl()]] with the [[hostInfo]].
	 *
	 * Note that unlike [[\yii\helpers\Url::toRoute()]], this method always treats the given route
	 * as an absolute route.
	 *
	 * @param string|array $params use a string to represent a route (e.g. `site/index`),
	 * or an array to represent a route with query parameters (e.g. `['site/index', 'param1' => 'value1']`).
	 * @param string $scheme the scheme to use for the url (either `http` or `https`). If not specified
	 * the scheme of the current request will be used.
	 * @return string the created URL
	 * @see createUrl()
	 */
	public function createAbsoluteUrl($params, $scheme = null)
	{
		$params = (array) $params;
		$url = $this->createUrl($params);

		if (strpos($url, '/@static') !== false) {
			$url = str_replace('/@static', '', $url);
		}

		if (strpos($url, '://') === false) {
			$url = $this->getHostInfo() . $url;
		}

		if (is_string($scheme) && ($pos = strpos($url, '://')) !== false) {
			$url = $scheme . substr($url, $pos);
		}

		return $url;
	}
	
	/**
	 * Returns the base URL that is used by [[createUrl()]] to prepend to created URLs.
	 * It defaults to [[Request::baseUrl]].
	 * This is mainly used when [[enablePrettyUrl]] is true and [[showScriptName]] is false.
	 * @return string the base URL that is used by [[createUrl()]] to prepend to created URLs.
	 * @throws InvalidConfigException if running in console application and [[baseUrl]] is not configured.
	 */
	// public function getBaseUrl()
	// {
	// 	if ($this->_baseUrl === null) {
	// 		$this->_baseUrl = isset(Yii::$app->params['urlStatic']) ? Yii::$app->params['urlStatic'] : '/static';
	// 	}
		
	// 	return $this->_baseUrl;
	// }
}