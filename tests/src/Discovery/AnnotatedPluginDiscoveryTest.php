<?php

/**
 * @file
 * Contains \EclipseGc\PluginAnnotation\Test\Discovery\AnnotatedPluginDiscoveryTest.
 */

namespace EclipseGc\PluginAnnotation\Test\Discovery;


use EclipseGc\Plugin\Test\Utility\TestFactoryResolver;
use EclipseGc\PluginAnnotation\Discovery\AnnotatedPluginDiscovery;

class AnnotatedPluginDiscoveryTest extends \PHPUnit_Framework_TestCase {

  public function testAnnotatedDiscovery() {
    /** @var \EclipseGc\Plugin\Test\Utility\AbstractPluginDictionary $dictionary */
    $dictionary = $this->getMockForAbstractClass('\EclipseGc\Plugin\Test\Utility\AbstractPluginDictionary');
    $directory = __DIR__;
    $directory_path = explode(DIRECTORY_SEPARATOR, $directory);
    array_pop($directory_path);
    $directory = implode(DIRECTORY_SEPARATOR, $directory_path);
    $namespaces = [
      'EclipseGc\PluginAnnotation\Test' => $directory,
    ];
    $namespaces = new \ArrayIterator($namespaces);
    $discovery = new AnnotatedPluginDiscovery($namespaces, 'Plugin' . DIRECTORY_SEPARATOR . 'Foo', 'EclipseGc\PluginAnnotation\Test\FooInterface', 'EclipseGc\PluginAnnotation\Test\Annotation\Foo');
    $dictionary->setDiscovery($discovery);
    $dictionary->setFactoryClass('EclipseGc\PluginAnnotation\Test\Factory\Foo');
    $dictionary->setFactoryResolver(new TestFactoryResolver());
    print_r($dictionary->createInstance('baz'));
  }
}
