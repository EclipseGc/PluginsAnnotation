<?php

/**
 * @file
 * Contains \EclipseGc\PluginAnnotation\Test\Plugin\Foo\Foo.
 */

namespace EclipseGc\PluginAnnotation\Test\Plugin\Foo;

use EclipseGc\Plugin\PluginDefinitionInterface;
use EclipseGc\PluginAnnotation\Test\Annotation\Foo as FooAnnotation;
use EclipseGc\PluginAnnotation\Test\Annotation\SubAnnotation;
use EclipseGc\PluginAnnotation\Test\FooInterface;

/**
 * @FooAnnotation(
 *   pluginId = "foo",
 *   arg1 = "Test",
 *   factory = "EclipseGc\PluginAnnotation\Test\Factory\Foo",
 *   subAnnotation = @SubAnnotation("test")
 * )
 */
class Foo implements FooInterface {

  /**
   * @var \EclipseGc\Plugin\PluginDefinitionInterface
   */
  protected $definition;

  public function __construct(PluginDefinitionInterface $definition) {
    $this->definition = $definition;
  }

  public function getPluginId() : string {
    return $this->definition->getPluginId();
  }

  /**
   * @return \EclipseGc\Plugin\PluginDefinitionInterface
   */
  public function getPluginDefinition() : PluginDefinitionInterface {
    return $this->definition;
  }

}
