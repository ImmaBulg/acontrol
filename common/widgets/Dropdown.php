<?php

namespace common\widgets;

use Yii;
use yii\helpers\ArrayHelper;
use common\helpers\Html;

class Dropdown extends \yii\bootstrap\Dropdown
{
	/**
	 * @inheritdoc
	 */
	protected function renderItems($items, $options = [])
	{
		$lines = [];
		foreach ($items as $i => $item) {
			if (isset($item['visible']) && !$item['visible']) {
				continue;
			}
			if (is_string($item)) {
				$lines[] = $item;
				continue;
			}
			if (!array_key_exists('label', $item)) {
				throw new InvalidConfigException("The 'label' option is required.");
			}
			$encodeLabel = isset($item['encode']) ? $item['encode'] : $this->encodeLabels;
			$label = $encodeLabel ? Html::encode($item['label']) : $item['label'];
			$itemOptions = ArrayHelper::getValue($item, 'options', []);
			$linkOptions = ArrayHelper::getValue($item, 'linkOptions', []);
			$linkOptions['tabindex'] = '-1';
			$url = array_key_exists('url', $item) ? $item['url'] : null;
			if (empty($item['items'])) {
				if ($url === null) {
					$content = $label;
					Html::addCssClass($itemOptions, 'dropdown-header');
				} else {
					$content = Html::a($label, $url, $linkOptions);
				}
			} else {
				$submenuOptions = $options;
				unset($submenuOptions['id']);
				$content = Html::a($label, $url === null ? '#' : $url, $linkOptions)
					. $this->renderItems($item['items'], $submenuOptions);
				Html::addCssClass($itemOptions, 'dropdown-submenu');
			}

			$lines[] = Html::tag('li', $content, $itemOptions);
		}

		return Html::tag('ul', implode("\n", $lines), $options);
	}
}