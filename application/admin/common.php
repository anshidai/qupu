<?php

/**
* 校验菜单权限
* @param string|array $currPermis 当前权限
* @param array $permis 所有权限
*/
function checkMenuPermis($currPermis, $permis)
{
	$halt = false;

	if (empty($currPermis)) {
		return $halt;
	}

	if (is_string($currPermis)) {
		$currPermis = [$currPermis];
	}

	for ($i = 0; $i < count($currPermis); $i++) {
		if (!empty($permis[$currPermis[$i]])) {
			$halt = true;
			break;
		}
	}

	return $halt;
}