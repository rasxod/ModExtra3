<?php
/**
*/

$migration = '002';
echo "\r\n START migration {$migration} \r\n";
// $modx->runProcessor('\\processors\\mgr\\groups\\create', 
// 	[
// 		'label' => 'DEV'
// 	], 
// 	[
// 		'processors_path' => MODX_CORE_PATH.'components/clientconfig'
// 	]);
$modx->addPackage('clientconfig', MODX_CORE_PATH.'components/clientconfig/model/');

$cgSetting_obj = $modx->getObject('cgSetting', ['key' => 'vers']);
if ($cgSetting_obj != null) {
	$new_vers = floatval($cgSetting_obj->get('value')) + 0.01;
	$cgSetting_obj->set('value', $new_vers);
	$cgSetting_obj->save();
	$modx->invokeEvent('ClientConfig_ConfigChange');
	$modx->getCacheManager()->delete('clientconfig',array(xPDO::OPT_CACHE_KEY => 'system_settings'));
}


echo "\r\n END migration {$migration} - ";
return true;