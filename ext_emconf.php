<?php

$EM_CONF['jar_pretty_preview'] = array(
	'title' => 'Pretty Preview',
	'description' => 'Generates an automatic pretty preview of content elements in the backend based on the TCA fields',
	'category' => 'plugin',
	'author' => 'JAR Media GmbH',
	'author_email' => 'info@jar.media',
	'version' => '1.0.0',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => '0',
	'createDirs' => '',
	'clearCacheOnLoad' => 0,
	'constraints' => array(
		'depends' => array(
			'typo3' => '10.4',
			'php' => '7.4.0-7.4.999',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);