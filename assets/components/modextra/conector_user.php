<?php

/** @var  MODX\Revolution\modX $modx */
/** @var  VoiVil\VoiVil $VoiVil */

if (file_exists(dirname(__FILE__, 4) . '/config.core.php')) {
    require_once dirname(__FILE__, 4) . '/config.core.php';
} else {
    require_once dirname(__FILE__, 5) . '/config.core.php';
}

require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
define('MODX_API_MODE', true);
require_once MODX_CONNECTORS_PATH . 'index.php';

header('Content-Type: application/json; charset=utf-8');

if ($modx->user->get('id') == 0 || !str_contains($_REQUEST['action'], 'VoiVil\\Processors\\Voite\\')) {
    echo json_encode(['success' => false, 'message' => 'Доступ запрещен']);
    die();
}

$VoiVil = $modx->services->get('VoiVil');
$modx->lexicon->load('voivil:default');

// handle request
$path = $modx->getOption(
    'processorsPath',
    $VoiVil->config,
    $modx->getOption('core_path') . 'components/voivil/' . 'Processors/'
);
$data = $modx->runProcessor($_REQUEST['action'], $_REQUEST, ['processors_path' => $path])->getResponse();
// $modx->log(1, $_REQUEST['action']);
if (is_array($data)) {
    echo json_encode($data);
} else {
    echo $data;
}
