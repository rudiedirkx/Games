<?php

function _mapsOptions($_maps, $_selected, $_empty = '') {
	$html = '';
	$_empty and $html = '<option value="">' . htmlspecialchars($_empty) . '</option>';
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
		'   fnf     ',
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
		'5  nfn2    211  ',
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
		'   2   f101112  ',
		'       n2000022 ',
		'       f100112  ',
		'        2111 32 ',
		'                ',
	),
	array(
		'0001   ',
		'000112 ',
		'000001 ',
		'000111 ',
		'0001f  ',
		'0001   ',
		'1111   ',
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
		'00000022311001  5  f       ',
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
		'   1001  fnf     ',
		'   20012         ',
		'   20113         ',
		'   212 4         ',
		'                 ',
	),
	array(
		'0000001  22                   ',
		'000111134 3                   ',
		'0001 101 3 n                  ',
		'00022201122                   ',
		'1101 111213                   ',
		' 202332 2 2f        3211      ',
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
		'00011102 33 32f3f  ',
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
		' fnf     ',
		'112111111',
		'000000000',
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
		'02 313f20',
		'02  fnf  ',
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
		'02 f',
		'024f',
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
	array(
		'000000',
		'001221',
		'001  2',
		'01234 ',
		'01 23 ',
		'012fn ',
		'001n  ',
		'001n  ',
		'001f  ',
	),
	array(
		'0000000',
		'0112110',
		'01 3 20',
		'1224 20',
		'fnnf210',
		'   n210',
		'   n 20',
		'     20',
	),
	array(
		'001f ',
		'002n ',
		'013f ',
		'01  2',
		'01222',
		'00000',
	),
	array(
		'12111  101 11 ',
		' 2 2232112211 ',
		'1212 1002 201n',
		'001221003 301 ',
		'222 10002 201 ',
		'  21100011101 ',
		'3310000000013 ',
		' 21000000001  ',
		'2 10000000012 ',
		'1110000000001 ',
		'0000122211012 ',
		'00001  2 101  ',
		'0000122211124 ',
		'12122222222 4 ',
		'fnf           ',
	),
	array(
		'0112f',
		'01 3 ',
		'0112f',
		'0012 ',
		'002  ',
		'002  ',
		'00122',
		'00000',
	),
	array(
		'0000000000',
		'0111000000',
		'02 3110000',
		'02 3 10000',
		'1233332100',
		'2 4 3ff210',
		'     f4f10',
		'     n3110',
		'     f1000',
	),
	array(
		' nf10',
		'  210',
		'  310',
		'   10',
		'  520',
		'   20',
		' n 20',
		'  210',
		'  210',
		' 4 10',
		'2 210',
		'11100',
		'00000',
	),
	array(
		'     ',
		'f  ff',
		'1113f',
		'00011',
		'00000',
	),
	array(
		'1221000',
		'1  3211',
		'13ff f ',
		'01     ',
	),
	array(
		'         ',
		'   212fff',
		' 4210124n',
		'11000001f',
		'00011212n',
		'0012 2 2 ',
		'113 3212 ',
		'1 4 4222 ',
		'334 3  2 ',
		'  212222 ',
		'22100012 ',
		'0000001  ',
	),
	array(
		'01 ',
		'01 ',
		'01n',
		'02f',
		'02f',
		'01n',
		'01 ',
		'01 ',
		'01 ',
		'01 ',
	),
	array(
		'           ',
		' 11223 311 ',
		' 200011101 ',
		' 200000112f',
		' 2211001 3 ',
		'  3 200112f',
		' 23 200001 ',
		' 332211111 ',
		'           ',
	),
	array(
		'  fnf ',
		' 2121 ',
		' 1001 ',
		' 4222 ',
		'      ',
	),
	array(
		'         ',
		'         ',
		' f11111  ',
		' n20001f ',
		' f10002n ',
		'  21111f ',
		'         ',
		'         ',
	),
	array(
		'0001     ',
		'00012    ',
		'1110112  ',
		'2f32111  ',
		'2f nfn   ',
	),
	array(
		' 2 2 2 210',
		' 312122 10',
		'f200001110',
		' 100111111',
		' 2222 11 1',
		'  f   2122',
		'      212 ',
		'          ',
	),
	array(
		'     22 1',
		'  n   311',
		' 3113 200',
		' 20011100',
		'110000000',
	),
	array(
		' nnn 22 1',
		'  1   311',
		' 3113 200',
		' 20011100',
		'110000000',
	),
	array(
		'112      ',
		'1 4      ',
		'12       ',
		'013      ',
		'013    f ',
		'01  2132 ',
		'0133202f ',
		'001 103  ',
		'0022314  ',
		'001 2 3  ',
		'0011222  ',
	),
	array(
		'00000000000',
		'00001222121',
		'01112  2 2 ',
		'02 34 4212 ',
		'02  4 3221 ',
		'014 52n    ',
		'002   n    ',
	),
	array(
		' 2 10011',
		' 222102 ',
		' 11 102 ',
		' 2221011',
		' 3 10011',
		'  21001 ',
		'  100011',
		'  123321',
		'  2    1',
		'  223321',
		'  100011',
		'  21001 ',
		'   31222',
		'    fnfn',
	),
	array(
		'         10',
		'        320',
		'         10',
		'        420',
		'         20',
		'    212  20',
		'    3122210',
		'    3 10000',
		'     210000',
		'    3322100',
		'   ff2  100',
		' nnn4322100',
		' n2ff100000',
		' f322100000',
		'  200000000',
		'  100000000',
	),
	array(
		'                  211f100',
		'               nn f112210',
		'        312  2123 5422f10',
		'       310122101fffff3210',
		'       3110100013f433f100',
		'       4 2110000111011100',
		'        5 310000000000000',
		'           10000000000000',
		'      fnn 310000000000000',
		' 113 4 33 310000000000000',
		' 212 312   10000000000000',
		'  21110124320000000000000',
		'  21110001 10000000000000',
		' 321 10001110000000000000',
		'  22210000000000000000000',
		' 22 100000000000000000000',
	),
	array(
		'1     ',
		'12    ',
		'12 ff ',
		'1 2221',
		'222000',
	),
	array(
		'00000000000012n',
		'0000000000001ff',
		'00000000000013 ',
		'00000000000001 ',
		'00000000000001n',
		'00000011111101 ',
		'0000001 33 212 ',
		'00000012  4  4 ',
		'000000024 4    ',
		'00000001 3 3   ',
		'00000001122    ',
		'00000000112    ',
		'122211013 3    ',
		'2  2 212       ',
		'  433  2       ',
		'      112      ',
	),
	array(
		'0012    ',
		'001     ',
		'0012    ',
		'0001    ',
		'0002    ',
		'0002f   ',
		'0112n   ',
		'02f3n   ',
		'03f     ',
		'02f4f   ',
		'01122   ',
		'00013   ',
		'0002    ',
		'0002 32 ',
		'0001112 ',
		'0000001 ',
	),
	array(
		'       20',
		'       30',
		'       20',
		' 32112210',
		' 10000000',
		' 21110000',
		' 32f10000',
		' nf210000',
		'  n200000',
		'  f100000',
	),
	array(
		'            ',
		'  3         ',
		'234fnnf3222 ',
		'2 f2112f22  ',
		'2 310012 34 ',
		'1110000112  ',
		'00000000123 ',
		'000000001 3 ',
		'0000000012  ',
		'00000000012 ',
		'00000000001 ',
	),
	array(
		'01 33  11n3f10',
		'012  3333ff210',
		'0012212  n4200',
		'0000002  ff210',
		'00000012344 10',
		'0000000001 210',
		'00000000011100',
		'00000000000000',
	),
	array(
		'0000000000000000',
		'1111100000000000',
		' 32 100000000000',
		'   3210000000000',
		'   3 20000000000',
		'   4 20000000000',
		'    310000001110',
		'    200111012f10',
		'   21002f322f210',
		'   21102ffnf3210',
		'   3 2122   3f10',
		'            f210',
	),
	array(
		'0001f  ',
		'0112n  ',
		'02f32  ',
		'03f    ',
		'02f32  ',
		'01333  ',
		'001    ',
		'0013 4 ',
		'000113 ',
		'0000011',
		'0000000',
	),
	array(
		'000001f     ',
		'000002n nfnf',
		'001233f 3232',
		'012   33f11f',
		'02 7 42 2111',
		'02   2111000',
		'012321000000',
		'000000000000',
	),
	array(
		'0123211   ',
		'01   32n  ',
		'0125   432',
		'0003 42  1',
		'0003 31232',
		'0013 4211 ',
		'001 3  221',
		'0011223 10',
		'0000001110',
		'0000000000',
	),
	array(
		'    3  210',
		'  5 434 10',
		'  334 2110',
		'  2  21000',
		'  22210000',
		'  10000000',
		'  21100000',
		'  2 100000',
		'  21100000',
		'fn10000000',
		'2 10000000',
		'1 10000000',
		'1110000000',
		'1 10000000',
		'3 31000000',
		'fff1000000',
	),
	array(
		'0000000000000',
		'0000001111000',
		'0000112 32100',
		'00001 33  310',
		'0000223 4ff31',
		'00123 3233f  ',
		'002  22 22n  ',
		'014 6433  n  ',
		'01     3     ',
		'0123333 n    ',
	),
	array(
		'  311 100000012  22100000000',
		'   22110001111 44 2 00000000',
		'  5 1000222 113 312100000000',
		'  311003  32202 323200001221',
		' 210000000000000000000123  1',
		' 1000000000000000000002  421',
		' 1000000011111211000113  310',
		' 212110012 12 3 21212 344 10',
		' 2 4 2001 433 312 4 32   320',
		'     31013  222223  32  nf10',
		'    3 2224 411 2 33     n231',
		'     23ff4 201133       nf  ',
		'        nf421112            ',
		'         f  n               ',
	),
	array(
		'0000000000000000',
		'0111000000001110',
		'02 4321000001 21',
		'03    21001122  ',
		'02 5 5 2123 22  ',
		'0112   n  f     ',
		'00122           ',
		'012             ',
		'02 4            ',
		'02              ',
		'012             ',
		'012             ',
		'02              ',
		'03              ',
		'02 3            ',
		'0112            ',
	),
	array(
		'00002  ',
		'00002 f',
		'000013 ',
		'000001 ',
		'000123n',
		'0002   ',
		'0113   ',
		'02 43  ',
		'02     ',
	),
	array(
		'01111 ',
		'02 32 ',
		'03   n',
		'02    ',
	),
	array(
		'2333100',
		'    200',
		'  nf200',
		'   3321',
		'   4ff1',
		'  ff321',
		'    100',
	),
	array(
		'000000000001  ',
		'000000000012  ',
		'00000000001 2 ',
		'0000000000123 ',
		'000000011101  ',
		'00000012 1013 ',
		'0000002 31112 ',
		'0000002 323 2 ',
		'00000123  f   ',
		'000001 3      ',
		'00000224f     ',
		'000001 2n     ',
		'00011323n     ',
		'0002 3 3      ',
		'1113 43       ',
		'              ',
	),
	array(
		'00000012   ',
		'0000001 5  ',
		'000000113  ',
		'0000000012 ',
		'0000000002 ',
		'0000011102 ',
		'000002 201 ',
		'011102 311 ',
		'12 1123  n ',
		'  211 2    ',
		'  4232     ',
		' fff       ',
	),
	array(
		'011211000000',
		'02 4 1000000',
		'02  42000000',
		'013  3221100',
		'00234ff4f100',
		'002f44ff4210',
		'002f  n 3 20',
	),
	array(
		'000000000000',
		'011223321000',
		'03f3ffff2100',
		'03f53n   210',
		'02f  n    10',
	),
	array(
		'012n',
		'01ff',
		'013 ',
		'112 ',
		'1 2n',
		'112 ',
		'012 ',
		'23  ',
		'  3 ',
		'    ',
	),
	array(
		'001        ',
		'012        ',
		'01 3       ',
		'0113       ',
		'0013 4nfnf ',
		'001   f321 ',
		'001246f301 ',
		'01222 f301 ',
		'01  34f202 ',
		'01222 2102 ',
		'0112433112 ',
		'013   2 23 ',
		'01   443   ',
		'1233       ',
		'1 22       ',
		'112        ',
	),
	array(
		'01n ',
		'12ff',
		'1f3n',
		'112n',
		'002f',
		'002f',
		'123n',
		'1ffn',
	),
	array(
		'00001110',
		'00113 20',
		'112 4 41',
		'1f334  1',
		'12fnf432',
		'01   2f1',
	),
	array(
		'nnf  n  n  n ',
		'f4f32f22f212f',
		'2f21111111011',
		'1110000000000',
	),
	array(
		' fnf ',
		' 121 ',
		' 101 ',
		' 101 ',
		' 223 ',
		'     ',
	),
	array(
		'         ',
		'12 2122nn',
		'011101 3 ',
		'00000124 ',
		'1121001ff',
		'111 1013 ',
		' 1111001 ',
	),
	array(
		'01f ',
		'013f',
		'002 ',
		'012 ',
		'02fn',
		'02f ',
	),
	array(
		'         ',
		'n1122211n',
		'n2100001f',
		'ff101112n',
		' 3101 22f',
		' 100112fn',
	),
	array(
		'  f  n    ',
		'  431n    ',
		'12 11n    ',
		'01111f    ',
		'00002n    ',
		'00012f    ',
		'0001 3    ',
		'00012 23  ',
		'000011112 ',
		'00000001  ',
		'00000001  ',
	),
	array(
		'  nnf  n   ',
		'  33f433   ',
		'233f3 3    ',
		'01 2214 54 ',
		'0111002 3  ',
		'0000001122 ',
	),
	array(
		'  f nfn ',
		'13f4312 ',
		'012f1011',
		'00111000',
	),
	array(
		'   n  f',
		'122214f',
		'000002f',
		'       ',
	),
	array(
		'ffnf',
		'224f',
		'f22f',
		'f211',
	),
	array(
		'1 33 ',
		'12   ',
		'0123 ',
		'0002 ',
		'0002f',
		'0001n',
		'0112n',
		'01 2 ',
		'012  ',
		'001  ',
	),
	array(
		'1 2   ',
		'113n  ',
		'012f  ',
		'01 221',
		'011100',
	),
	array(
		'00001 ',
		'01222 ',
		'02  2n',
		'02 4  ',
		'0123  ',
		'001 2n',
		'00112 ',
		'00001 ',
		'00001n',
		'00001 ',
		'00013 ',
		'0002ff',
		'1112  ',
		'2 333 ',
		'2   f ',
		'12    ',
	),
	array(
		'    ',
		'  3 ',
		'222 ',
		'002n',
		'001 ',
		'002 ',
		'013f',
		'01f ',
		'012 ',
	),
	array(
		'        ',
		' 21121  ',
		'11001   ',
		'00001 2 ',
		'0000112 ',
		'00001 2 ',
		'00001   ',
	),
	array(
		'              ',
		'  2122 222113 ',
		'  20011100001 ',
		' f21100000001 ',
		' n3 310000001 ',
		' n2  10000002 ',
		' 112210000013 ',
		' 10111000002  ',
		' 212 1000013  ',
		' 3 31100012 3 ',
		'   2000001 22 ',
		'  21000001111 ',
	),
);
