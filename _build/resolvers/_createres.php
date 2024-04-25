<?php

/** @var xPDO\Transport\xPDOTransport $transport */
/** @var array $options */
/** @var  MODX\Revolution\modX $modx */

/**
 * 
 */
use MODX\Revolution\modX;

class CreateRes 
{
	private $modx;
	private $conf;
	private $path;
	private $res_list = [];
	private $menuindex = 0;
	
	function __construct(&$modx, $conf = [])
	{
		$this->modx = $modx;
		$this->conf = $conf;
		$this->path = MODX_CORE_PATH . 'components/'.$this->conf['name_lower'].'/elements/resources/';
	}

	private function getIndex($parent = 0){
		$res_q = $this->modx->newQuery(modResource::class);
		$res_q->select(['ind' => 'MAX(`menuindex`)']);
		$res_q->where(['parent' => $parent]);
		$res_q->limit(1);
		$res_index = $this->modx->getObject(modResource::class, $res_q);
		// $this->menuindex = $res_index->get('ind');
		return intval($res_index->get('ind'));
	}

	private function getResList(){
		$this->res_list = include($this->path . 'resources.php');
		if (!is_array($this->res_list)) {
			$this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in Resources');
			return false;
		}
		return true;
	}
	private function getContent($filename)
    {
        if (file_exists($filename)) {
            $file = trim(file_get_contents($filename));

            return preg_match('#\<\?php(.*)#is', $file, $data)
                ? rtrim(rtrim(trim(@$data[1]), '?>'))
                : $file;
        }

        return '';
    }

	private function addRes($data, $uri, $parent) {
		$file = $data['context_key'] . '/' . $uri;
		/** @var modResource $resource */
		if ($data['template'] && !is_numeric($data['template'])) {
			$_template_obj = $this->modx->getObject(modTemplate::class, ['templatename' => $data['template']]);
			if ($_template_obj != null) {
				$data['template'] = $_template_obj->get('id');
			}
		}
		if (isset($data['id'])) {
			$resource = $this->modx->getObject(modResource::class, ['id' => $data['id']]);
		} else {
			print_r($data);
			$resource = $this->modx->getObject(modResource::class, ['alias' => $data['alias']]);
		}
		// $modx->log(modX::LOG_LEVEL_INFO, print_r($data, true));

		if ($resource == null) {
			$resource = $this->modx->newObject(modResource::class);
			if (isset($data['id'])) {
				$resource->set('id', $data['id']);
			}
		} else {
			print 'UPD RES';
			unset($data['menuindex']);
		}
		// return;
		$context = $data['context_key'];
		$context_count = $this->modx->getCount('modContext', ['key' => $data['context_key']]);
		print_r($data);
		$resource->fromArray(array_merge([
			'parent' => $parent,
			'published' => ($data['published']) ? $data['published'] : false,
			'deleted' => false,
			'hidemenu' => ($data['hidemenu']) ? $data['hidemenu'] : false,
			'createdon' => time(),
			'template' => ($data['template']) ? $data['template'] : 1,
			'isfolder' => !empty($data['isfolder']) || !empty($data['resources']),
			'uri' => $uri,
			'uri_override' => false,
			'richtext' => false,
			'searchable' => true,
			'content' => $this->getContent($this->path . $file . '.tpl'),
		], $data), '', true, true);
		$resource->save();
		$groups_list = $resource->getResourceGroupNames();
		if (!empty($data['groups'])) {
			foreach ($data['groups'] as $group) {
				if (!$resource->isMember($group)) {
					$resource->joinGroup($group);
				}
				if (in_array($group, $groups_list)) {
					unset($groups_list[array_search($group, $groups_list)]); 
				}
			}
		} else {
			foreach ($data['groups'] as $group) {
				$resource->joinGroup($group);
				if (in_array($group, $groups_list)) {
					unset($groups_list[array_search($group, $groups_list)]); 
				}
			}

		}
		if (count($groups_list)) {
			foreach ($groups_list as $gkey => $g) {
				$resource->leaveGroup($g);
			}
		}
		$resource->save();
		// $resources[] = $resource;

		if (!empty($data['resources'])) {
			$menuindex = $this->getIndex($parent)+1;
			foreach ($data['resources'] as $alias => $item) {
				$parent = $resource->get('id');
				$item_temp = $this->setItem($item, $context, $parent);
				$item_temp['alias'] = $alias;
				$this->addRes($item_temp, $uri . '/' . $alias, $parent);
				unset($item_temp);
			}
		}

		return true;
	}

	private function setItem($item, $context = 'web', $parent = 0){
		if (!isset($item['id'])) {
		//     $item['id'] = $this->_idx++;
			$_res = $this->modx->getObject(modResource::class, ['alias' => $alias]);
			if ($_res != null) {
				$item['id'] = $_res->get('id');
			}
			unset($_res);
		}
		
		$menuindex = $this->getIndex($parent)+1;
		$item['context_key'] = $context;
		$item['menuindex'] = $menuindex;
		return $item;
	}
	public function start(){
		$this->getResList();

		foreach ($this->res_list as $context => $items) {
			foreach ($items as $alias => $item) {
				$parent = ($item['parent'] > 0) ? $item['parent'] : 0;
				$item_temp = $this->setItem($item, $context, $parent);
				$item_temp['alias'] = $alias;
				$this->addRes($item_temp, $alias, $parent);
				unset($item_temp);
			}
		}
	}
}

if ($transport->xpdo) {
	$modx = $transport->xpdo;
	switch ($options[xPDOTransport::PACKAGE_ACTION]) {
		case xPDOTransport::ACTION_INSTALL:
		case xPDOTransport::ACTION_UPGRADE:
			$createRes = new CreateRes($modx, ['name_lower' => 'modextra']);
			$createRes->start();
			break;

		case xPDOTransport::ACTION_UNINSTALL:
			break;
	}
}

return true;