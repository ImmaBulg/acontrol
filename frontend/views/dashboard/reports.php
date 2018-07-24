<?php

use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\widgets\GridView;
use common\widgets\DataColumn;
use common\models\Report;
use common\models\ReportFile;
use common\components\rbac\Role;

$this->title = Yii::t('frontend.view', 'Reports');
?>
<div class="wrap">
	<?php echo $this->render('_header'); ?>
	<div id="main">
		<div class="container">
			<div class="col-lg-12">
				<?php echo $this->render('_switch', [
					'action' => ['reports'],
					'user' => $user,
					'form' => $form_switch,
					'show_clients' => true,
					'show_sites' => true,
					'show_tenants' => ($user->role == Role::ROLE_TENANT),
					'show_meters' => false,
					'show_channels' => false,
				]); ?>
				<h1 class="page-header"><?php echo Yii::t('frontend.view', 'Reports'); ?></h1>
				<?php echo GridView::widget([
					'dataProvider' => $data_provider,
					'filterModel' => $filter_model,
					'id' => 'table-reports',
					'layout' => "{items}\n{pager}",
					'columns' => [
						[
							'attribute' => 'site_owner_name',
							'format' => 'raw',
							'value' => function($model){
								return $model->relationSiteOwner->name;
							},
						],
						[
							'attribute' => 'site_name',
							'format' => 'raw',
							'value' => function($model){
								return $model->relationSite->name;
							},
						],
						[
							'attribute' => 'tenant_name',
							'format' => 'raw',
							'value' => function($model){
								if ($model->level != Report::LEVEL_SITE && ($tenant = $model->getRelationTenantReports()->one()) != null) {
									return $tenant->relationTenant->name;
								}
							},
							'visible' => ($user->role == Role::ROLE_TENANT),
						],
						[
							'attribute' => 'type',
							'value' => 'aliasType',
							'filter' => Report::getListTypes(),
						],
						[
							'attribute' => 'from_date',
							'format' => 'date',
							'filterType' => DataColumn::FILTER_CELL_TYPE_DATE,
						],
						[
							'attribute' => 'to_date',
							'format' => 'date',
							'filterType' => DataColumn::FILTER_CELL_TYPE_DATE,
						],
						[
							'format' => 'raw',
							'value' => function($model){
								$btn = [];
								
								if (($pdf = $model->getFilePath(ReportFile::FILE_TYPE_PDF)) != null) {
									$btn[] = Html::a(Yii::t('frontend.view', 'PDF'), $pdf, ['class' => 'btn btn-sm btn-info', 'target' => '_blank']);
								}
								if (($excel = $model->getFilePath(ReportFile::FILE_TYPE_EXCEL)) != null) {
									$btn[] = Html::a(Yii::t('frontend.view', 'Excel'), $excel, ['class' => 'btn btn-sm btn-info', 'target' => '_blank']);
								}
								if (($dat = $model->getFilePath(ReportFile::FILE_TYPE_DAT)) != null) {
									$btn[] = Html::a(Yii::t('frontend.view', 'DAT'), $dat, ['class' => 'btn btn-sm btn-info', 'target' => '_blank']);
								}

								return '<div class="btn-toolbar pull-right">' .implode('', $btn). '</div>';
							},
						],
					],
				]); ?>
			</div>
		</div>
	</div>
</div>