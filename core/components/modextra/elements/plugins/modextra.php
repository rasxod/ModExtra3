<?php

/** @var \MODX\Revolution\modX $modx */
switch ($modx->event->name) {
	case 'OnLoadWebDocument':
		$modx->regClientScript(MODX_ASSETS_URL.'components/modextra/js/web/modextra.js?v=9');
		$modx->regClientHTMLBlock('<script type="text/javascript">
				$(document).ready(function(){
					// modextra.init()
				});
			</script> 
			');
		break;
}
