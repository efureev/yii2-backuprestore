<?php
use kartik\file\FileInput;
use yii\helpers\Html;
use yii\widgets\ActiveForm;


/* @var $this yii\web\View */
/* @var $model app\models\Document */
/* @var $form yii\widgets\ActiveForm */

$this->params ['breadcrumbs'] [] = [
	'label' => 'Управление',
	'url' => [
		'index'
	]
];
$this->params['breadcrumbs'][] = [
	'label' => 'Загрузить дамп',
	'url' => ['upload'],
]; ?>

<h1>Загрузить дамп</h1>


<? $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>


<?= $form->field($model, 'upload_file')->widget(FileInput::classname(), [
	'options' => ['accept' => 'mysql/*.sql'],
]); ?>


<div class="form-group">
	<?=
	Html::submitButton('Сохранить',
		['class' => 'btn btn-success']
	) ?>
</div>

<? ActiveForm::end(); ?>

<!-- form -->
