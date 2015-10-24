<?php

function _mapsOptions($_maps, $_selected) {
	$html = '';
	foreach ( $_maps as $i => $map ) {
		$selected = $i == (int)$_selected ? ' selected' : '';
		$html .= '<option value="' . $i . '"' . $selected . '>Map ' . ($i+1) . ' (' . strlen($map[0]) . 'x' . count($map) . ')</option>';
	}
	return $html;
}

return array(
	array(
		'           ',
		' 2112      ',
		' 2001      ',
		' 41011212  ',
		'  10000012 ',
		'  20001111 ',
		'  21212 21 ',
		'           ',
	),
	array(
		'    100001    10',
		'    10000112  20',
		'    42100001  10',
		'      1000012210',
		'  22333210000000',
		'111001  22210000',
		'000001233  31000',
		'11100001    1000',
		'  201111  4 2000',
		'2 201       1000',
		'33201      11000',
		'  321      10111',
		'5     2    211  ',
		'                ',
		'             1  ',
		'               1',
	),
	array(
		'                ',
		'   21111        ',
		' 211001         ',
		' 100001         ',
		' 210011 112     ',
		'  3212  101 12  ',
		'   2    101112  ',
		'        2000022 ',
		'        100112  ',
		'        2111 32 ',
		'                ',
	),
	array(
		'0001   ',
		'000112 ',
		'000001 ',
		'000111 ',
		'0001   ',
		'0001   ',
		'1111   ',
		'       ',
		'       ',
		'  47   ',
		'       ',
	),
	array(
		' 11 11 11 101110000001 101 ',
		'1111111122213 211100011101 ',
		'000011101 11  21 211001122 ',
		'00112 101111221123 3322 3  ',
		'001 2111221000001 3   33   ',
		'00111012  2000123223       ',
		'0000001 4 20013  223       ',
		'00000022311001  5          ',
		'1100112 1000014            ',
		' 3212 311111114            ',
		'      3111 11 3            ',
		'        11 111             ',
		'                           ',
	),
	array(
		'            ',
		' fnf4n      ',
		' 1212 2     ',
		' 100112     ',
		' 310002     ',
		'  10002   2 ',
		'  100011    ',
		'  211001    ',
		'    2111    ',
		'            ',
		'      3     ',
		'      2     ',
		'            ',
	),
	array(
		'                 ',
		' 111224 21110001 ',
		' 200001 10000001 ',
		' 311001 212222 1 ',
		'   1001          ',
		'   20012         ',
		'   20113         ',
		'   212 4         ',
		'                 ',
	),
	array(
		'0000001  22                   ',
		'000111134 3                   ',
		'0001 101 3                    ',
		'00022201122                   ',
		'1101 111213                   ',
		' 202332 2 2         3211      ',
		' 201  21223          101   332',
		'1112433222 22       3112  3 10',
		'001 3 5  32123      211 33 210',
		'00224   5 312 3  322 222 21100',
		'001 22323 3 33 22 123 11110000',
		'0011100123322 211122 210011111',
		'00111012 2 12220001 320001 11 ',
		'002 433 22111 111112 111122222',
		'002    210001111 212111 23 32 ',
		'0013  310000000112 10012 3 3 2',
	),
	array(
		'1 1000112  100001  ',
		'1110124 3221001123 ',
		'01122   2000001 12 ',
		'01 2 3321111123211 ',
		'0112110001 11  112 ',
		'12321111022212211  ',
		'1   32 101 1000013 ',
		'124 3 210111001112 ',
		'001232100112111 22 ',
		'0002 20012 3 112   ',
		'0002 2012 4 3213   ',
		'00011102 33 32 3f  ',
		'00122103 3112 3    ',
		'123  212 31122     ',
		'    5 223          ',
		'                   ',
	),
	array(
		'     ',
		'0000 ',
		'1221 ',
		'1    ',
		'13n  ',
		'01f  ',
		'01n  ',
	),
	array(
		'000000000',
		'112111111',
		' fnf     ',
	),
	array(
		' n100',
		' f100',
		' n200',
		' f210',
		' 4 20',
		'   20',
	),
	array(
		'   100',
		'  f100',
		'   200',
		'  f210',
		' 22 10',
	),
	array(
		'   n100',
		'fnnf210',
		'1234 10',
		'01  210',
		'0122100',
		'0000000',
	),
	array(
		'011100',
		'02 311',
		'03  f ',
		'02    ',
		'012332',
		'000000',
	),
	array(
		'000000000',
		'011101110',
		'02 313 20',
		'02  f    ',
	),
	array(
		'000000',
		'011100',
		'01 211',
		'023nfn',
		'01 f  ',
		'013   ',
		'002   ',
		'002   ',
		'002   ',
		'001   ',
	),
	array(
		'02    ',
		'03 f  ',
		'02  31',
		'012210',
		'000000',
	),
	array(
		'01f   ',
		'02n   ',
		'12fnfn',
		'1 2211',
		'111000',
		'000000',
	),
	array(
		'     10000000000',
		'     10111000000',
		'     212 1011111',
		'     nfn2101 12 ',
		'       f1001112 ',
		'       n21000011',
		'       n 3110000',
		'   nnnfn 4 10000',
		'   n1124 3110111',
		'fnnf322 210001 2',
		'2213  223210123 ',
		' 103 411  101 32',
		'1103 301221012 1',
		'0002 31100001332',
		'000123 100001  1',
		'00001 2100001221',
	),
	array(
		'012 ',
		'02  ',
		'02  ',
		'024 ',
		'01 n',
		'012 ',
		'001 ',
	),
	array(
		' f 10',
		'  310',
		' f100',
		'  200',
		' f210',
		'12 10',
		'01110',
		'00000',
	),
	array(
		'  223321',
		'  4    1',
		' f 43321',
		'13 20000',
		'01110000',
		'00000000',
	),
	array(
		'000000',
		'011110',
		'01 211',
		'023 f ',
		'02    ',
		'02    ',
	),
	array(
		'001110',
		'113 20',
		' f  30',
		'113 20',
		'001110',
		'000000',
	),
);
