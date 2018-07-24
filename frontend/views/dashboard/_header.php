<?php

use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use common\helpers\Html;
use backend\widgets\i18n\LanguageNav;
?>

<div id="header-nav">
	<?php NavBar::begin([
		'options' => [
			'id' => 'header-nav-bar',
			'class' => 'navbar-inverse',
		],
	]); ?>
		<?php echo Nav::widget([
				'options' => [
					'id' => 'main-nav',
					'class' => 'navbar-nav',
				],
				'items' => [
					[
						'label' => Yii::t('frontend.view', 'Real time'),
						'url' => ['/dashboard/index'],
					],
					[
						'label' => Yii::t('frontend.view', 'History consumption'),
						'url' => ['/dashboard/history-consumption'],
					],
					[
						'label' => Yii::t('frontend.view', 'Reports'),
						'url' => ['/dashboard/reports'],
					],
				],
			]);
			echo Nav::widget([
				'options' => [
					'id' => 'settings-nav',
					'class' => 'navbar-nav navbar-right',
				],
				'items' => [
					[
						'label' => Yii::t('frontend.view', 'Logout'),
						'url' => ['/user-auth/logout'],
						'linkOptions' => ['data-method' => 'post'],
					],
				],
			]);
			echo LanguageNav::widget([
				'options' => [
					'id' => 'language-nav',
					'class' => 'navbar-nav navbar-right',
				],
		]);	?>

	<?php NavBar::end(); ?>
</div>
