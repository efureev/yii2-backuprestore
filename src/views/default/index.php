<div class="backup-default-index">

	<?
	$this->params ['breadcrumbs'] [] = [
		'label' => 'Управление',
		'url' => [
			'index'
		]
	];

	if (!empty(Yii::$app->controller->menu)) {
		echo \yii\widgets\Menu::widget([
			'items' => Yii::$app->controller->menu
		]);
	}

	if (Yii::$app->session->hasFlash('success')): ?>
		<div class="alert alert-success">
			<?= Yii::$app->session->getFlash('success'); ?>
		</div>
	<? endif; ?>


	<div class="row">
		<div class="col-md-12">
			<?= $this->render('_list', [
				'dataProvider' => $dataProvider
			]);
			?>
		</div>
	</div>

</div>