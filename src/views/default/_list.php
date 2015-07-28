<?php

use kartik\grid\GridView;
use yii\helpers\Html;

/** @var \yii\data\ArrayDataProvider $dataProvider */
echo GridView::widget([
	'id' => 'install-grid',
	'export' => false,
	'dataProvider' => $dataProvider,
	'resizableColumns' => false,
	'showPageSummary' => false,
	'headerRowOptions' => ['class' => 'kartik-sheet-style'],
	'filterRowOptions' => ['class' => 'kartik-sheet-style'],
	'responsive' => true,
	'hover' => true,
	'panel' => [
		'heading' => '<h3 class="panel-title">Дампы БД</h3>',
		'type' => 'primary',
		'showFooter' => false
	],

	'toolbar' => [
		['content' =>
			Html::a('<i class="glyphicon glyphicon-plus"></i>  Создать дамп', ['create'], ['class' => 'btn btn-success create-backup']) . ' ' .
			Html::a('<i class="glyphicon glyphicon-plus"></i>  Загрузить дамп', ['upload'], ['class' => 'btn btn-success']),
		],
	],
	'columns' => [
		[
			'header' => 'Файл',
			'value' => 'name',
		],
		[
			'header' => 'Размер',
			'value' => 'size',
			'format' => 'size',
		],
		[
			'header' => 'Создан',
			'value' => 'create_time'
		],
		[
			'header' => 'Когда',
			'value' => 'modified_time',
			'format' => 'relativeTime',
		],
		[
			'class' => 'kartik\grid\ActionColumn',
			'template' => '{restore_action}',
			'header' => 'Восстановить',
			'buttons' => [
				'restore_action' => function ($url, $model) {
					return Html::a('<span class="glyphicon glyphicon-import"></span>', $url, [
						'title' => 'Восстановить дамп',
					]);
				}
			],
			'urlCreator' => function ($action, $model, $key, $index) {
				if ($action === 'restore_action') {
					$url = Yii::$app->urlManager->createUrl(['backuprestore/default/restore', 'file' => $model['name']]);
					return $url;
				}
				return null;
			}
		],
		[
			'class' => 'kartik\grid\ActionColumn',
			'template' => '{download_action}',
			'header' => 'Скачать',
			'buttons' => [
				'download_action' => function ($url, $model) {
					return Html::a('<span class="glyphicon glyphicon-download-alt"></span>', $url, [
						'title' => 'Скачать дамп',
					]);
				}
			],
			'urlCreator' => function ($action, $model, $key, $index) {
				if ($action === 'download_action') {
					$url = Yii::$app->urlManager->createUrl(['backuprestore/default/download', 'file' => $model['name']]);
					return $url;
				}
				return null;
			}
		], [
			'class' => 'kartik\grid\ActionColumn',
			'template' => '{delete_action}',
			'header' => 'Удалить',
			'buttons' => [
				'delete_action' => function ($url, $model) {
					return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, [
						'title' => 'Удалить дамп',
					]);
				}
			],
			'urlCreator' => function ($action, $model, $key, $index) {
				if ($action === 'delete_action') {
					$url = Yii::$app->urlManager->createUrl(['backuprestore/default/delete', 'file' => $model['name']]);
					return $url;
				}
				return null;
			}
		],
	]
]);