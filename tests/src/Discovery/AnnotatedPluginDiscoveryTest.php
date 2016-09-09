<?php

/**
 * @file
 * Contains \EclipseGc\PluginAnnotation\Test\Discovery\AnnotatedPluginDiscoveryTest.
 */

namespace EclipseGc\PluginAnnotation\Test\Discovery;


use EclipseGc\Plugin\Test\Utility\TestFactoryResolver;
use EclipseGc\PluginAnnotation\Discovery\AnnotatedPluginDiscovery;
use EclipseGc\PluginAnnotation\Exception\NonexistentAnnotationException;
use EclipseGc\PluginAnnotation\Exception\NonexistentInterfaceException;

class AnnotatedPluginDiscoveryTest extends \PHPUnit_Framework_TestCase {

  /**
   * @covers \EclipseGc\PluginAnnotation\Discovery\AnnotatedPluginDiscovery::__construct
   * @covers \EclipseGc\PluginAnnotation\Discovery\AnnotatedPluginDiscovery::findPluginImplementations
   * @covers \EclipseGc\PluginAnnotation\Discovery\AnnotatedPluginDiscovery::extractClassNames
   * @covers \EclipseGc\PluginAnnotation\Discovery\AnnotatedPluginDiscovery::extractNamespace
   */
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
    $this->assertEquals(3, count($dictionary->getDefinitions()));
    $this->assertEquals('Test', $dictionary->getDefinition('foo')->getProperty('arg1'));
    $this->assertEquals('Test', $dictionary->createInstance('foo')->getPluginDefinition()->getProperty('arg1'));
  }

  public function testAnnotatedDiscoveryInterfaceException() {
    $interface = 'EclipseGc\PluginAnnotation\Test\BarInterface';
    $namespaces = new \ArrayIterator([]);
    $this->expectException(NonexistentInterfaceException::class);
    $this->expectExceptionMessage(sprintf("The specified interface %s does not exist.", $interface));
    new AnnotatedPluginDiscovery($namespaces, 'Plugin' . DIRECTORY_SEPARATOR . 'Foo', $interface, 'EclipseGc\PluginAnnotation\Test\Annotation\Foo');
  }

  public function testAnnotatedDiscoveryAnnotationException() {
    $annotation = 'EclipseGc\PluginAnnotation\Test\Annotation\Bar';
    $namespaces = new \ArrayIterator([]);
    $this->expectException(NonexistentAnnotationException::class);
    $this->expectExceptionMessage(sprintf("The specified annotation class %s does not exist.", $annotation));
    new AnnotatedPluginDiscovery($namespaces, 'Plugin' . DIRECTORY_SEPARATOR . 'Foo', 'EclipseGc\PluginAnnotation\Test\FooInterface', $annotation);
  }

}
