<?php

/**
 * @file
 * Contains \EclipseGc\PluginAnnotation\Test\Factory\Foo.
 */

namespace EclipseGc\PluginAnnotation\Test\Factory;

use EclipseGc\Plugin\Factory\FactoryInterface;
use EclipseGc\Plugin\PluginDefinitionInterface;

class Foo implements FactoryInterface {
  public function createInstance(PluginDefinitionInterface $definition, ...$constructors) {
    $class = $definition->getClass();
    return new $class($definition, ...$constructors);
  }

}