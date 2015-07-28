<?php
namespace efureev\backuprestore\controllers;

use efureev\backuprestore\models\UploadForm;
use Yii;
use yii\data\ArrayDataProvider;
use yii\web\Controller;
use yii\web\UploadedFile;

/**
 * Class DefaultController
 *
 * @package efureev\backuprestore\controllers
 *
 * @property string $path Путь до папки с дампами
 */
class DefaultController extends Controller
{

	public $menu = [];
	public $tables = [];
	public $fp;
	public $file_name;
	public $_path = null;
	public $back_temp_file = 'db_backup_';

	protected function getPath()
	{
		if (isset($this->module->path))
			$this->_path = \Yii::getAlias($this->module->path);
		else
			$this->_path = Yii::$app->basePath . '/_backup';

		if (!file_exists($this->_path)) {
			mkdir($this->_path);
			chmod($this->_path, 0777);
		}
		return $this->_path . '/';
	}

	public function actionIndex()
	{
		$this->updateMenuItems();
		$path = $this->path;
		$dataArray = [];

		$list_files = glob($path . '*.sql');

		if ($list_files) {
			$list = array_map('basename', $list_files);
			sort($list);
			foreach ($list as $id => $filename) {
				$columns = [];
				$columns['id'] = $id;
				$columns['name'] = basename($filename);
				$columns['size'] = filesize($path . $filename);
				$columns['create_time'] = \Yii::$app->formatter->asDatetime(filectime($path . $filename));
				$columns['modified_time'] = filemtime($path . $filename);

				$dataArray[] = $columns;
			}
		}
		$dataProvider = new ArrayDataProvider(['allModels' => $dataArray]);

		return $this->render('index', [
			'dataProvider' => $dataProvider,
		]);
	}


	public function actionDelete($file)
	{
		$this->updateMenuItems();

		$sqlFile = $this->path . basename($file);

		if (file_exists($sqlFile)) {
			@unlink($sqlFile);
			$flashError = 'success';
			$flashMsg = 'The file ' . $sqlFile . ' was successfully deleted.';
		} else {
			$flashError = 'error';
			$flashMsg = 'The file ' . $sqlFile . ' was not found.';
		}

		\Yii::$app->getSession()->setFlash($flashError, $flashMsg);
		$this->redirect(['index']);
	}


	public function actionCreate()
	{
		$tables = $this->getTables();

		if (!$this->StartBackup()) {
			\Yii::$app->getSession()->setFlash('error', 'Ошибка создания дампа');
			return $this->render('index');
		}

		foreach ($tables as $tableName) {
			$this->getColumns($tableName);
		}
		foreach ($tables as $tableName) {
			$this->getData($tableName);
		}
		$this->EndBackup();

		\Yii::$app->getSession()->setFlash('success', 'Дамп создан');

		$this->redirect(['index']);
	}


	public function actionRestore($file)
	{
		$this->updateMenuItems();

		$sqlFile = $this->path . basename($file);
		$this->execSqlFile($sqlFile);

		\Yii::$app->getSession()->setFlash('success', 'Дам воссоздан');
		$this->redirect(['index']);
	}


	private function execSqlFile($sqlFile)
	{
		$message = "ok";

		if (file_exists($sqlFile)) {
			$sqlArray = file_get_contents($sqlFile);

			$cmd = Yii::$app->db->createCommand($sqlArray);
			try {
				$cmd->execute();
			} catch (\Exception $e) {
				$message = $e->getMessage();
			}
		}
		return $message;
	}

	public function getColumns($tableName)
	{
		$sql = 'SHOW CREATE TABLE ' . $tableName;
		$cmd = Yii::$app->db->createCommand($sql);
		$table = $cmd->queryOne();

		$create_query = $table['Create Table'] . ';';

		$create_query = preg_replace('/^CREATE TABLE/', 'CREATE TABLE IF NOT EXISTS', $create_query);
		$create_query = preg_replace('/AUTO_INCREMENT\s*=\s*([0-9])+/', '', $create_query);
		if ($this->fp) {
			$this->writeComment('TABLE `' . addslashes($tableName) . '`');
			$final = 'DROP TABLE IF EXISTS `' . addslashes($tableName) . '`;' . PHP_EOL . $create_query . PHP_EOL . PHP_EOL;
			fwrite($this->fp, $final);
		} else {
			$this->tables[$tableName]['create'] = $create_query;
			return $create_query;
		}
	}

	public function getData($tableName)
	{
		$sql = 'SELECT * FROM ' . $tableName;
		$cmd = Yii::$app->db->createCommand($sql);
		$dataReader = $cmd->query();

		$data_string = '';

		foreach ($dataReader as $data) {
			$itemNames = array_keys($data);
			$itemNames = array_map("addslashes", $itemNames);
			$items = join('`,`', $itemNames);
			$itemValues = array_values($data);
			$itemValues = array_map("addslashes", $itemValues);
			$valueString = join("','", $itemValues);
			$valueString = "('" . $valueString . "'),";
			$values = "\n" . $valueString;
			if ($values != "") {
				$data_string .= "INSERT INTO `$tableName` (`$items`) VALUES" . rtrim($values, ",") . ";" . PHP_EOL;
			}
		}

		if ($data_string == '')
			return null;

		if ($this->fp) {
			$this->writeComment('TABLE DATA ' . $tableName);
			$final = $data_string . PHP_EOL . PHP_EOL . PHP_EOL;
			fwrite($this->fp, $final);
		} else {
			$this->tables[$tableName]['data'] = $data_string;
			return $data_string;
		}
	}

	/**
	 * @return array
	 */
	public function getTables()
	{
		return Yii::$app->db->createCommand('SHOW TABLES')->queryColumn();
	}

	public function StartBackup($addcheck = true)
	{
		$this->file_name = $this->path . $this->back_temp_file . date('Y.m.d_H.i.s') . '.sql';

		$this->fp = fopen($this->file_name, 'w+');

		if ($this->fp == null)
			return false;
		fwrite($this->fp, '-- -------------------------------------------' . PHP_EOL);
		if ($addcheck) {
			fwrite($this->fp, 'SET AUTOCOMMIT=0;' . PHP_EOL);
			fwrite($this->fp, 'START TRANSACTION;' . PHP_EOL);
			fwrite($this->fp, 'SET SQL_QUOTE_SHOW_CREATE = 1;' . PHP_EOL);
		}
		fwrite($this->fp, 'SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;' . PHP_EOL);
		fwrite($this->fp, 'SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;' . PHP_EOL);
		fwrite($this->fp, '-- -------------------------------------------' . PHP_EOL);
		$this->writeComment('START BACKUP');
		return true;
	}

	public function EndBackup($addcheck = true)
	{
		fwrite($this->fp, '-- -------------------------------------------' . PHP_EOL);
		fwrite($this->fp, 'SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;' . PHP_EOL);
		fwrite($this->fp, 'SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;' . PHP_EOL);

		if ($addcheck) {
			fwrite($this->fp, 'COMMIT;' . PHP_EOL);
		}
		fwrite($this->fp, '-- -------------------------------------------' . PHP_EOL);
		$this->writeComment('END BACKUP');
		fclose($this->fp);
		$this->fp = null;
	}

	public function writeComment($string)
	{
		fwrite($this->fp, '-- -------------------------------------------' . PHP_EOL);
		fwrite($this->fp, '-- ' . $string . PHP_EOL);
		fwrite($this->fp, '-- -------------------------------------------' . PHP_EOL);
	}


	public function actionClean()
	{
		if (!isset($this->module->ignoreTables) || !is_array($this->module->ignoreTables) || empty($this->module->ignoreTables)) {
			$this->module->ignoreTables = [];
		}
		$tables = $this->getTables();

		if (!$this->StartBackup()) {
			Yii::$app->user->setFlash('success', "Error");
			return $this->render('index');
		}

		$message = '';

		foreach ($tables as $tableName) {
			if (in_array($tableName, $this->module->ignoreTables))
				continue;
			fwrite($this->fp, '-- -------------------------------------------' . PHP_EOL);
			fwrite($this->fp, 'DROP TABLE IF EXISTS ' . addslashes($tableName) . ';' . PHP_EOL);
			fwrite($this->fp, '-- -------------------------------------------' . PHP_EOL);

			$message .= $tableName . ',';
		}
		$this->EndBackup();

		Yii::$app->user->logout();

		$this->execSqlFile($this->file_name);
		unlink($this->file_name);
		$message .= ' удалены.';
		Yii::$app->session->setFlash('success', $message);
		return $this->redirect(['index']);
	}


	public function actionDownload($file)
	{
		$this->updateMenuItems();

		$sqlFile = $this->path . basename($file);

		if (file_exists($sqlFile))
			Yii::$app->response->sendFile($sqlFile);
	}


	public function actionSyncdown()
	{
		$tables = $this->getTables();

		if (!$this->StartBackup()) {
			return $this->render('index');
		}

		foreach ($tables as $tableName) {
			$this->getColumns($tableName);
		}
		foreach ($tables as $tableName) {
			$this->getData($tableName);
		}
		$this->EndBackup();
		return $this->actionDownload(basename($this->file_name));
	}


	public function actionUpload()
	{
		$model = new UploadForm();
		if (isset($_POST['UploadForm'])) {
			$model->attributes = $_POST['UploadForm'];
			$model->upload_file = UploadedFile::getInstance($model, 'upload_file');
			if ($model->upload_file->saveAs($this->path . $model->upload_file)) {
				return $this->redirect(['index']);
			}
		}

		return $this->render('upload', ['model' => $model]);
	}

	protected function updateMenuItems()
	{
		switch ($this->action->id) {
			case 'restore':
				$this->menu[] = array('label' => Yii::t('app', 'View Site'), 'url' => Yii::$app->HomeUrl);
				break;

			case 'create':
				$this->menu[] = array('label' => Yii::t('app', 'Список дампов'), 'url' => array('index'));
				break;

			case 'upload':
				$this->menu[] = array('label' => Yii::t('app', 'Создать дамп'), 'url' => array('create'));
				break;

			default:
				$this->menu[] = array('label' => Yii::t('app', 'Список дампов'), 'url' => array('index'));
				$this->menu[] = array('label' => Yii::t('app', 'Загрузить дамп'), 'url' => array('upload'));
				$this->menu[] = array('label' => Yii::t('app', 'Очистить БД'), 'url' => array('clean'));
		}
	}

}
