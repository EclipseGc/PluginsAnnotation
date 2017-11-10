<?php

/**
 * @file
 * Contains \EclipseGc\PluginAnnotation\Test\Annotation\Foo.
 */

namespace EclipseGc\PluginAnnotation\Test\Annotation;

use EclipseGc\PluginAnnotation\Definition\AnnotatedPluginDefinition;

/**
 * @Annotation
 */
class Foo extends AnnotatedPluginDefinition {

  protected $arg1;

  protected $subAnnotation;

}
