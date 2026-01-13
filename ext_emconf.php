<?php

$EM_CONF['jar_pretty_preview'] = array(
	'title' => 'Pretty Preview',
	'description' => 'Generates an automatic pretty preview of content elements in the backend based on the TCA fields.',
	'category' => 'plugin',
	'author' => 'invokable GmbH',
	'author_email' => 'info@invokable.gmbh',
	'version' => '3.0.0',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => '0',
	'createDirs' => '',
	'clearCacheOnLoad' => 0,
	'constraints' => array(
		'depends' => array(
			'typo3' => '13.4.1-13.4.99',
			'jar_utilities' => '3.0.0-3.99.99'
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);
