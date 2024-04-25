<?php
/**
*/

$migration = '002';
echo "\r\n START migration {$migration} \r\n";
$modx->addPackage('clientconfig', MODX_CORE_PATH.'components/clientconfig/model/');

$_group_obj = $modx->getObject('cgGroup', ['label' => 'DEV']);
if ($_group_obj == null) {
	$_group_obj = $modx->newObject('cgGroup');
	$_group_obj->set('label', 'DEV');
	$_group_obj->set('description', 'для разработчиков');
	$_group_obj->set('sortorder', 0);
	$_group_obj->save();
}
$_group = $_group_obj->get('id');
if (intval($_group) == 0) {
	return false;
}
// echo '$_group-'.$_group;

$cgSetting_obj = $modx->getObject('cgSetting', ['key' => 'vers']);
if ($cgSetting_obj == null) {
	$cgSetting_obj = $modx->newObject('cgSetting');
	$cgSetting_obj->set('key', 'vers');
	$cgSetting_obj->set('label', 'vers');
	$cgSetting_obj->set('xtype', 'textfield');
	$cgSetting_obj->set('is_required', 0);
	$cgSetting_obj->set('description', '{\'vers\' | option}');
	$cgSetting_obj->set('value', '0.01');

	$cgSetting_obj->set('group', $_group);
	$cgSetting_obj->save();
	$modx->invokeEvent('ClientConfig_ConfigChange');
	$modx->getCacheManager()->delete('clientconfig',array(xPDO::OPT_CACHE_KEY => 'system_settings'));
}


echo "\r\n END migration {$migration} - ";
return true;