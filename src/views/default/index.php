<div class="backup-default-index">

	<?
	$this->params ['breadcrumbs'] [] = [
		'label' => 'Manage',
		'url' => [
			'index'
		]
	];
	?>

	<? if (Yii::$app->session->hasFlash('success')): ?>
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