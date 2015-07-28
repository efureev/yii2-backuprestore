<?php
namespace efureev\backuprestore;

class Module extends \yii\base\Module
{
	public $path = null;

	public $ignoreTables = ['user', 'migration'];

	public $controllerNamespace = 'efureev\backuprestore\controllers';

	public function init()
	{
		parent::init();

		//\Yii::configure($this, require(__DIR__ . '/config.php'));
	}
}
