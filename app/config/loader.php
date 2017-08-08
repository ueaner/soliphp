<?php

// Composer autoloader
$autoloader = require $config['application']['vendorDir'] . 'autoload.php';

// Register test classes
// 命名空间开头不可以有"\"反斜线，结尾必须有"\"反斜线，目录以"/"斜杠结尾
$autoloader->addPsr4("", $config['application']['controllersDir']);
$autoloader->addPsr4("", $config['application']['modelsDir']);
$autoloader->addPsr4("", $config['application']['libraryDir']);
$autoloader->addPsr4("", $config['application']['tasksDir']);
