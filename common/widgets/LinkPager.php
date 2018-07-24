<?php

namespace common\widgets;

use Yii;
use yii\helpers\Html;

class LinkPager extends \yii\widgets\LinkPager
{
	public $options = ['class' => 'pagination'];
	public $wrapOptions = [];
	public $nextPageLabel = true;
	public $prevPageLabel = true;
	public $firstPageLabel = true;
	public $lastPageLabel = true;

	/**
	 * Renders the page buttons.
	 * @return string the rendering result
	 */
	protected function renderPageButtons()
	{
		$pageCount = $this->pagination->getPageCount();
		if ($pageCount < 2 && $this->hideOnSinglePage) {
			return '';
		}

		$buttons = [];
		$currentPage = $this->pagination->getPage();

		// first page
		$firstPageLabel = $this->firstPageLabel === true ? Yii::t('common.common', 'First') : $this->firstPageLabel;
		if ($firstPageLabel !== false) {
			$buttons[] = $this->renderPageButton($firstPageLabel, 0, $this->firstPageCssClass, $currentPage <= 0, false);
		}

		// prev page
		if ($this->prevPageLabel !== false) {
			if (($page = $currentPage - 1) < 0) {
				$page = 0;
			}
			$buttons[] = $this->renderPageButton(Yii::t('common.common', 'Prev'), $page, $this->prevPageCssClass, $currentPage <= 0, false);
		}

		// internal pages
		list($beginPage, $endPage) = $this->getPageRange();
		for ($i = $beginPage; $i <= $endPage; ++$i) {
			$buttons[] = $this->renderPageButton($i + 1, $i, null, false, $i == $currentPage);
		}

		// next page
		if ($this->nextPageLabel !== false) {
			if (($page = $currentPage + 1) >= $pageCount - 1) {
				$page = $pageCount - 1;
			}
			$buttons[] = $this->renderPageButton(Yii::t('common.common', 'Next'), $page, $this->nextPageCssClass, $currentPage >= $pageCount - 1, false);
		}

		// last page
		$lastPageLabel = $this->lastPageLabel === true ? Yii::t('common.common', 'Last') : $this->lastPageLabel;
		if ($lastPageLabel !== false) {
			$buttons[] = $this->renderPageButton($lastPageLabel, $pageCount - 1, $this->lastPageCssClass, $currentPage >= $pageCount - 1, false);
		}

		return Html::tag('ul', implode("\n", $buttons), $this->options);
	}
}