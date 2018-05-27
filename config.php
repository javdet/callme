<?php 
return array(

	'CallMeDEBUG' => 1, // дебаг сообщения в логе: 1 - пишем, 0 - не пишем
	'tech' => 'SIP', 
	'authToken' => 'zptl6iw2iy8ogxw8gyybiiqr9ip3kdi4', //токен авторизации битрикса
	'bitrixApiUrl' => 'https://a2s.bitrix24.ru/rest/10/telt9b743dqhf1tb/', //url к api битрикса (входящий вебхук)
	'extentions' => array('70408108', '70408109', '73832099320', '78432168710', '78312351776', '74732122505', '73433394070', '78126655160', '78126655189'), // список внешних номеров, через запятую
	'context' => 'to_219_vmb', //исходящий контекст для оригинации звонка
	'intPrefix' => '219', // Префикс для внутренних номеров
	'asterisk' => array( // настройки для подключения к астериску
		    'host' => '127.0.0.1',
		    'scheme' => 'tcp://',
		    'port' => 5038,
		    'username' => 'callme',
		    'secret' => 'p4o3Nb49TeYzFpxY',
		    'connect_timeout' => 10000,
		    'read_timeout' => 10000
		),
	'listener_timeout' => 300, //скорость обработки событий от asterisk

);
