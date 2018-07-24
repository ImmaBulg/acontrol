<?php
use yii\bootstrap\Nav;
use common\helpers\Html;
use common\widgets\ActiveForm;
use backend\models\forms\FormSearch;
use backend\widgets\search\SearchView;

$this->title = Yii::t('backend.view', 'Search');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-header">
	<?php if($result[$form->type]->totalCount): ?>
		<h2 class="list-group-item-heading text-muted pull-right">
			<?php echo Yii::t('backend.view', "We've found {n, plural, =1{# result} =3{# results} other{# results}}", [
				'n' => $result[$form->type]->totalCount,
			]); ?>
		</h2>
	<?php endif; ?>
	<h1><?php echo $this->title; ?></h1>
</div>
<div class="row">
	<div class="col-lg-3">
		<?php echo Nav::widget([
			'options' => [
				'id' => 'search-nav',
				'class' => 'nav-pills nav-stacked',
			],
			'encodeLabels' => false,
			'items' => [
				[
					'label' => Yii::t('backend.view', 'Clients'). ' ' .Html::tag('span', $result[FormSearch::CLIENTS]->totalCount, ['class' => 'badge']),
					'url' => $form->getAliasUrl(FormSearch::CLIENTS),
				],
				[
					'label' => Yii::t('backend.view', 'Sites'). ' ' .Html::tag('span', $result[FormSearch::SITES]->totalCount, ['class' => 'badge']),
					'url' => $form->getAliasUrl(FormSearch::SITES),
				],
				[
					'label' => Yii::t('backend.view', 'Tenants'). ' ' .Html::tag('span', $result[FormSearch::TENANTS]->totalCount, ['class' => 'badge']),
					'url' => $form->getAliasUrl(FormSearch::TENANTS),
				],
				[
					'label' => Yii::t('backend.view', 'Meters'). ' ' .Html::tag('span', $result[FormSearch::METERS]->totalCount, ['class' => 'badge']),
					'url' => $form->getAliasUrl(FormSearch::METERS),
				],
			],
		]); ?>
	</div>
	<div class="col-lg-9">
		<?php echo SearchView::widget([
			'dataProvider' => $result[$form->type],
			'id' => 'list-search',
			'itemView' => 'item-view/search',
			'viewParams' => ['type' => $form->type],
			'emptyText' => Html::tag('h2', Yii::t('backend.view', 'No results found'), ['class' => 'list-group-item-heading text-muted']),
			'highlightText' => $form->q,
		]); ?>			
	</div>
</div>