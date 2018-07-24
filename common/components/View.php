<?php

namespace common\components;

use Yii;
use common\helpers\Html;

/**
 * View represents a view object in the MVC pattern.
 *
 * View provides a set of methods (e.g. [[render()]]) for rendering purpose.
 *
 * View is configured as an application component in [[\yii\base\Application]] by default.
 * You can access that instance via `Yii::$app->view`.
 *
 * You can modify its configuration by adding an array to your application config under `components`
 * as it is shown in the following example:
 *
 * ~~~
 * 'view' => [
 *     'theme' => 'app\themes\MyTheme',
 *     'renderers' => [
 *         // you may add Smarty or Twig renderer here
 *     ]
 *     // ...
 * ]
 * ~~~
 *
 */
class View extends \yii\web\View
{
	public function title()
	{
		if ($this->title != null) {
			if (!is_array($this->title)) {
				$this->title = [$this->title];
			}

			$this->title = implode(' - ', array_merge($this->title, [Yii::$app->name]));
		} else {
			$this->title = Yii::$app->name;
		}

		
		echo Html::encode($this->title);
	}
}