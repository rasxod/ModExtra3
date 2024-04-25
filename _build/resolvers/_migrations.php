<?php
/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
/*
\core\components\pdotools\src\CoreTools.php
82 'elementsPath' => $this->modx->getOption('pdotools_elements_path', null, MODX_CORE_PATH . 'elements/', true),
86 // $this->config['elementsPath'] = $this->modx->getOption('pdotools_elements_path', null, MODX_CORE_PATH . 'elements/', true);
*/


if ($transport->xpdo) {
	$modx =& $transport->xpdo;
	$path = MODX_CORE_PATH.'components/modextra/migrations/';
	$old_path = $modx->getOption('pdotools_elements_path');
	// $modx->setOption('pdotools_elements_path', $path);
	 // Add insert data
	$insert_files = scandir($path);
	$pdo = $modx->getService('pdoTools');
	$pdo->setConfig(['elementsPath' => $path]);
	ksort($insert_files);
	foreach ($insert_files as $F) {
		if ($F['0'] == '_') {
			@unlink($path.$F);
			$temp_file = mb_substr($F, 1);
			@unlink($path.$temp_file);
			unset($temp_file);
			continue;
		}
		if (!in_array($F, ['..', '.']) && in_array(substr($F, 1), $insert_files)) {
			@unlink($path.substr($F, 1));
			// code...
		}
	}
	foreach ($insert_files as $F) {
		if (!in_array($F, ['..', '.']) && $F['0'] != '_' && file_exists($path.$F) && pathinfo($path.$F, PATHINFO_EXTENSION) == 'php') {
			$modx->log(modX::LOG_LEVEL_INFO, print_r($pdo->runSnippet('@FILE '.$F), true));
		}
	}
	$pdo->setConfig(['elementsPath' => $old_path]);
	// $modx->setOption('pdotools_elements_path', $old_path);
}

return true;