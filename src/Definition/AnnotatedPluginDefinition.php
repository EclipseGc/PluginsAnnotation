<?php

/**
 * @file
 * Contains \EclipseGc\PluginAnnotation\Definition\AnnotatedPluginDefinition.
 */

namespace EclipseGc\PluginAnnotation\Definition;

use Doctrine\Common\Annotations\Annotation;
use EclipseGc\Plugin\PluginDefinitionInterface;

class AnnotatedPluginDefinition extends Annotation implements PluginDefinitionInterface {

  protected $pluginId;

  protected $class;

  protected $factory;

  /**
   * {@inheritdoc}
   */
  public function getProperties() : array {
    $properties = get_object_vars($this);
    unset($properties['value']);
    unset($properties['class']);
    unset($properties['factory']);
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function getProperty($name) {
    $properties = $this->getProperties();
    if (array_key_exists($name, $properties)) {
      return $this->$name;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginId() : string {
    return $this->pluginId;
  }

  /**
   * {@inheritdoc}
   */
  public function getClass() : string {
    return $this->class;
  }

  /**
   * {@inheritdoc}
   */
  public function getFactory() : string {
    return $this->factory;
  }

}
