<?php

$EM_CONF['jar_pretty_preview'] = array(
	'title' => 'Pretty Preview',
	'description' => 'Generates an automatic pretty preview of content elements in the backend based on the TCA fields.',
	'category' => 'plugin',
	'author' => 'invokable GmbH',
	'author_email' => 'info@invokable.gmbh',
	'version' => '1.0.10',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => '0',
	'createDirs' => '',
	'clearCacheOnLoad' => 0,
	'constraints' => array(
		'depends' => array(
			'typo3' => '10.4.1-11.5.99',
			'php' => '7.4.0-7.4.999',
			'jar_utilities' => '1.0.0'
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);