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
	'tagIds' => 'tagId',
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

// UTF-8文字列をUnicodeエスケープする。ただし英数字と記号はエスケープしない。
function unicode_decode($str) {
  return preg_replace_callback("/((?:[^\x09\x0A\x0D\x20-\x7E]{3})+)/", "decode_callback", $str);
}

function decode_callback($matches) {
  $char = mb_convert_encoding($matches[1], "UTF-16", "UTF-8");
  $escaped = "";
  for ($i = 0, $l = strlen($char); $i < $l; $i += 2) {
    $escaped .=  "\u" . sprintf("%02x%02x", ord($char[$i]), ord($char[$i+1]));
  }
  return $escaped;
}

// var_dumpを整形して表示
function customVarDump($object) {
	echo "<pre>";
  var_dump($object);
  echo "</pre>";
}

/**
 * Returns the HTTP Status code of $response
 * @param string $response
 * @return string
 */
function extract_response_http_code($response) {
    $tmp = explode('\n', $response);
    $array = explode(' ', $tmp[0]);

    return $array[1];
}

// インデントや改行をつけた綺麗なxmlのstringを返す
function returnFormattedXmlString ($xmlString) {
	// stringからDOM構築
	$dom = DOMDocument::loadXML($xmlString);
	
	// encodingが指定されてないと&#x30C6;&#x30B9;&#x30C8;みたいになるので何か指定する
	if (!$dom->encoding) {
	  $dom->encoding = 'UTF-8';
	}
	
	// 整形して出力するフラグ
	$dom->formatOutput = true;
	
	// 文字列で取得
	return $dom->saveXML();
}

// SimpleXMLElement Objectをarrayに変換する
function SimpleXMLElementArrayToArray($xmlArray) {
    $array = array();
    foreach ( $xmlArray as $value) {
        $json = json_encode($value);
        $array[] = json_decode($json,TRUE);
    }
    return $array;
}