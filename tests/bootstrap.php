<?php
/**
 * @file
 * Contains autoload.php
 */

/** @var \Composer\Autoload\Classloader $autoloader */
$autoloader = include __DIR__ . '/../vendor/autoload.php';
$autoloader->addPsr4('EclipseGc\PluginAnnotation\Test\\', __DIR__ . '/src');
$prefixes = $autoloader->getPrefixesPsr4();
$plugin_dir = $prefixes['EclipseGc\Plugin\\'][0];
$directory_path = explode(DIRECTORY_SEPARATOR, $plugin_dir);
array_pop($directory_path);
$directory_path[] = 'tests';
$directory_path[] = 'src';
$plugin_dir = implode(DIRECTORY_SEPARATOR, $directory_path);
$autoloader->addPsr4('EclipseGc\Plugin\Test\\', $plugin_dir);
return $autoloader;
