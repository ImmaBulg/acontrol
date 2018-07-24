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
class UrlManagerBackend extends \yii\web\UrlManager
{

}