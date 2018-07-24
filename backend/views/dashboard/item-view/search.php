<?php
use common\helpers\Html;
use backend\models\forms\FormSearch;
use backend\widgets\search\SearchDetailView;
?>
<?php

switch ($type) {
	case FormSearch::CLIENTS:
		echo SearchDetailView::widget([
			'model' => $model,
			'attributes' => [
				[
					'attribute' => 'name',
					'label' => false,
					'format' => 'raw',
					'value' => Html::tag('h3', Html::a($model->name, ['/client/view', 'id' => $model->id]), ['class' => 'list-group-item-heading search-result']),
				],
				[
					'attribute' => 'email',
					'format' => 'raw',
					'value' => Html::tag('span', $model->email, ['class' => 'search-result']),
				],
				[
					'attribute' => 'phone',
					'format' => 'raw',
					'value' => function($model){
						if ($model->relationUserProfile != null) {
							return Html::tag('span', $model->relationUserProfile->phone, ['class' => 'search-result']);
						}
					},
				],
			],
		]);
		break;

	case FormSearch::SITES:
		echo SearchDetailView::widget([
			'model' => $model,
			'attributes' => [
				[
					'attribute' => 'name',
					'label' => false,
					'format' => 'raw',
					'value' => Html::tag('h3', Html::a($model->name, ['/site/view', 'id' => $model->id]), ['class' => 'list-group-item-heading search-result']),
				],
				[
					'attribute' => 'user_name',
					'format' => 'raw',
					'value' => Html::a($model->relationUser->name, ['/client/view', 'id' => $model->relationUser->id], ['class' => 'search-result']),
				],
				[
					'attribute' => 'electric_company_id',
					'format' => 'raw',
					'value' => Html::tag('span', $model->electric_company_id, ['class' => 'search-result']),
				],
			],
		]);
		break;

	case FormSearch::TENANTS:
		echo SearchDetailView::widget([
			'model' => $model,
			'attributes' => [
				[
					'attribute' => 'name',
					'label' => false,
					'format' => 'raw',
					'value' => Html::tag('h3', Html::a($model->name, ['/tenant/view', 'id' => $model->id]), ['class' => 'list-group-item-heading search-result']),
				],
				[
					'attribute' => 'site_name',
					'format' => 'raw',
					'value' => Html::a($model->relationSite->name, ['/site/view', 'id' => $model->relationSite->id], ['class' => 'search-result']),
				],
				[
					'attribute' => 'user_name',
					'format' => 'raw',
					'value' => Html::a($model->relationUser->name, ['/client/view', 'id' => $model->relationUser->id], ['class' => 'search-result']),
				],
			],
		]);
		break;

	case FormSearch::METERS:
		echo SearchDetailView::widget([
			'model' => $model,
			'attributes' => [
				[
					'attribute' => 'name',
					'label' => false,
					'format' => 'raw',
					'value' => Html::tag('h3', Html::a($model->name, ['/meter/view', 'id' => $model->id]), ['class' => 'list-group-item-heading search-result']),
				],
				[
					'attribute' => 'tenants',
					'format' => 'raw',
					'value' => function($model){
						if ($model->relationMeterChannels != null) {
							$rows = [];

							foreach ($model->relationMeterChannels as $channel) {
								if ($channel->relationRuleSingleChannels != null) {
									foreach ($channel->relationRuleSingleChannels as $rule) {
										$list = [];
										$tenant = $rule->relationTenant;
										$site = $tenant->relationSite;
										$client = $site->relationUser;
										$list[] = Html::tag('li', '<span class="text-muted">' .$rule->getAttributeLabel('tenant_name'). ': </span>' .Html::a($tenant->name, ['/tenant/view', 'id' => $tenant->id]));
										$list[] = Html::tag('li', '<span class="text-muted">' .$tenant->getAttributeLabel('site_name'). ': </span>' .Html::a($site->name, ['/site/view', 'id' => $site->id]));
										$list[] = Html::tag('li', '<span class="text-muted">' .$site->getAttributeLabel('user_name'). ': </span>' .Html::a($client->name, ['/client/view', 'id' => $client->id]));
										$list[] = Html::tag('li', '<span class="text-muted">' .Yii::t('backend.view', 'Rule'). ': </span>' .Html::a(Yii::t('backend.view', 'Single channel association rule'), ['/rule-single-channel/list', 'id' => $tenant->id]));
										$rows[] = Html::tag('ul', implode("", $list));
									}
								}

								if ($channel->relationRuleGroupLoadsAsMeterChannel != null) {
									foreach ($channel->relationRuleGroupLoadsAsMeterChannel as $rule) {
										$list = [];
										$tenant = $rule->relationTenant;
										$site = $tenant->relationSite;
										$client = $site->relationUser;
										$list[] = Html::tag('li', '<span class="text-muted">' .$rule->getAttributeLabel('tenant_name'). ': </span>' .Html::a($tenant->name, ['/tenant/view', 'id' => $tenant->id]));
										$list[] = Html::tag('li', '<span class="text-muted">' .$tenant->getAttributeLabel('site_name'). ': </span>' .Html::a($site->name, ['/site/view', 'id' => $site->id]));
										$list[] = Html::tag('li', '<span class="text-muted">' .$site->getAttributeLabel('user_name'). ': </span>' .Html::a($client->name, ['/client/view', 'id' => $client->id]));
										$list[] = Html::tag('li', '<span class="text-muted">' .Yii::t('backend.view', 'Rule'). ': </span>' .Html::a(Yii::t('backend.view', 'Group load association rule'), ['/rule-group-load/list', 'id' => $tenant->id]));
										$rows[] = Html::tag('ul', implode("", $list));
									}
								}

								if ($channel->relationRuleGroupLoadsAsMeterChannelGroup != null) {
									foreach ($channel->relationRuleGroupLoadsAsMeterChannelGroup as $rule) {
										$list = [];
										$tenant = $rule->relationTenant;
										$site = $tenant->relationSite;
										$client = $site->relationUser;
										$group = $rule->relationMeterChannelGroup;
										$list[] = Html::tag('li', '<span class="text-muted">' .$rule->getAttributeLabel('tenant_name'). ': </span>' .Html::a($tenant->name, ['/tenant/view', 'id' => $tenant->id]));
										$list[] = Html::tag('li', '<span class="text-muted">' .$tenant->getAttributeLabel('site_name'). ': </span>' .Html::a($site->name, ['/site/view', 'id' => $site->id]));
										$list[] = Html::tag('li', '<span class="text-muted">' .$site->getAttributeLabel('user_name'). ': </span>' .Html::a($client->name, ['/client/view', 'id' => $client->id]));
										$list[] = Html::tag('li', '<span class="text-muted">' .$group->getAttributeLabel('group_name'). ': </span>' .Html::a($client->name, ['/site/meter-channel-groups', 'id' => $site->id]));
										$list[] = Html::tag('li', '<span class="text-muted">' .Yii::t('backend.view', 'Rule'). ': </span>' .Html::a(Yii::t('backend.view', 'Group load association rule'), ['/rule-group-load/list', 'id' => $tenant->id]));
										$rows[] = Html::tag('ul', implode("", $list));
									}
								}
							}

							return implode("", $rows);
						}
					},
				],
			],
		]);
		break;

	default:
		break;
}