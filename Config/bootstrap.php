<?php
App::uses('GaufretteLoader', 'FileStorage.Lib');
App::uses('StorageManager', 'FileStorage.Lib');
App::uses('LocalImageProcessingListener', 'FileStorage.Event');
App::uses('CakeEventManager', 'Event');

spl_autoload_register(__NAMESPACE__ .'\GaufretteLoader::load');

$listener = new LocalImageProcessingListener();
CakeEventManager::instance()->attach($listener);
