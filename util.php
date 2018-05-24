<?php

// ランダム文字列の生成
function randomStr($length = 8)
{
    return substr(str_shuffle('1234567890abcdefghijklmnopqrstuvwxyz'), 0, $length);
}


/* 複数形を単数形にするユーティリティ関数
/* http://pb-times.jp/P_527b395742f17
**/
$irregular_rules = array(
	'men'		=>	'man',
	'seamen'	=>	'seaman',
	'snowmen'	=>	'snowman',
	'women'		=>	'woman',
	'people'	=>	'person',
	'children'	=>	'child',
	'sexes'		=>	'sex',
	'moves'		=>	'move',
	'databases'	=>	'database',
	'feet'		=>	'foot',
	'cruces'	=>	'crux',
	'oases'		=>	'oasis',
	'phenomena'	=>	'phenomenon',
	'teeth'		=>	'tooth',
	'geese'		=>	'goose',
	'atlases'	=>	'atlas',
	'corpuses'	=>	'corpus',
	'genies'	=>	'genie',
	'genera'	=>	'genus',
	'graffiti'	=>	'graffito',
	'loaves'	=>	'loaf',
	'mythoi'	=>	'mythos',
	'niches'	=>	'niche',
	'numina'	=>	'numen',
	'octopuses'	=>	'octopus',
	'opuses'	=>	'opus',
	'penises'	=>	'penis',
	'equipment'	=>	'equipment',
	'information'	=>	'information',
	'rice'		=>	'rice',
	'money'		=>	'money',
	'species'	=>	'species',
	'series'	=>	'series',
	'fish'		=>	'fish',
	'sheep'		=>	'sheep',
	'swiss'		=>	'swiss',
);

$singular_rules = array(
	'(quiz)zes$'		=>	'$1',
	'(matr)ices$'		=>	'$1ix',
	'(vert|ind)ices$'	=>	'$1ex',
	'^(ox)en'		=>	'$1',
	'(alias|status)es$'	=>	'$1',
	'(octop|vir)i$'		=>	'$1us',
	'(cris|ax|test)es$'	=>	'$1is',
	'(shoe)s$'		=>	'$1',
	'(o)es$'		=>	'$1',
	'(bus)es$'		=>	'$1',
	'([m|l])ice$'		=>	'$1ouse',
	'(x|ch|ss|sh)es$'	=>	'$1',
	'movies$'		=>	'movie',
	'series$'		=>	'series',
	'([^aeiouy]|qu)ies$'	=>	'$1y',
	'([lr])ves$'		=>	'$1f',
	'(tive)s$'		=>	'$1',
	'(hive)s$'		=>	'$1',
	'([^f])ves$'		=>	'$1fe',
	'(^analy)ses$'		=>	'$1sis',
	'(analy|ba|diagno|parenthe|progno|synop|the)ses$'	=>	'$1sis',
	'([ti])a$'		=>	'$1um',
	'(n)ews$'		=>	'$1ews',
	'(.)s$'			=>	'$1',
);
function singularByPlural($plural) {
	global $irregular_rules;
	global $singular_rules;

	$singular = $plural;

	if (array_key_exists(strtolower($plural), $irregular_rules)) {
		$singular = $irregular_rules[strtolower($plural)];
	} else {
		foreach($singular_rules as $key => $value) {
			if (preg_match('/' . $key . '/', $plural)) {
				$singular = preg_replace('/' . $key . '/', $value, $plural);
				break;
			}
		}
	}

	return $singular;
}