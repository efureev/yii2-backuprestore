<?php
use yii\helpers\Html;


$this->params ['breadcrumbs'] [] = [
	'label' => 'Manage',
	'url' => [
		'index'
	]
];
$this->params['breadcrumbs'][] = [
	'label' => 'Restore',
	'url' => ['restore'],
]; ?>


<?
//$this->widget('bootstrap.widgets.TbButtonGroup', array(
//'buttons'=>$this->actions,
//'type'=>'success',
//'size'=>'mini',
//'htmlOptions'=>array('class'=>'pull-right')
//));
?>
<h1>
	<? //echo  $this->action->id; ?>
</h1>

<p>
	<? if (isset($error)) echo $error; else echo 'Done'; ?>
</p>
<p>

	<?= Html::a('View site', ['index'], ['class' => 'btn btn-warning']) ?>


	<? //echo Html::link('View Site',Yii::app()->HomeUrl)?>
</p>
