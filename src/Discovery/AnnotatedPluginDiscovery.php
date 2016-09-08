<?php

/**
 * @file
 * Contains \EclipseGc\PluginAnnotation\Discovery\AnnotatedPluginDiscovery.
 */

namespace EclipseGc\PluginAnnotation\Discovery;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use EclipseGc\Plugin\Discovery\PluginDefinitionSet;
use EclipseGc\Plugin\Discovery\PluginDiscoveryInterface;
use EclipseGc\Plugin\PluginDefinitionInterface;

class AnnotatedPluginDiscovery implements PluginDiscoveryInterface {

  /**
   * @var \Traversable
   */
  protected $namespaces;

  /**
   * @var string
   */
  protected $directory;

  /**
   * @var string
   */
  protected $interface;

  /**
   * @var string
   */
  protected $annotationClass;

  /**
   * AnnotatedPluginDiscovery constructor.
   */
  public function __construct(\Traversable $namespaces, string $directory, string $interface, string $annotationClass) {
    $this->namespaces = $namespaces;
    $this->directory = $directory;
    if (interface_exists($interface)) {
      $this->interface = $interface;
    }
    else {
      throw new \Exception(sprintf("The specified interface %s does not exist.", $interface));
    }
    if (class_exists($annotationClass)) {
      $this->annotationClass = $annotationClass;
    }
    else {
      throw new \Exception(sprintf("The specified annotation class %s does not exist."), $annotationClass);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function findPluginImplementations(PluginDefinitionInterface ...$definitions) : PluginDefinitionSet {
    // Clear the annotation loaders of any previous annotation classes.
    AnnotationRegistry::reset();
    // Register the namespaces of classes that can be used for annotations.
    AnnotationRegistry::registerLoader('class_exists');
    $reader = new AnnotationReader();
    foreach ($this->namespaces as $namespace => $directory) {
      $plugin_directory = "$directory/{$this->directory}";
      if (file_exists($plugin_directory)) {
        foreach (glob("$plugin_directory/*.php") as $file) {
          $file_contents = file_get_contents($file);
          $tokens = token_get_all($file_contents);
          $classes = $this->extractClassNames($tokens);
          foreach ($classes as $class) {
            if (!empty(class_implements($class)[$this->interface])) {
              $definition = $reader->getClassAnnotation(new \ReflectionClass($class), $this->annotationClass);
              if ($definition) {
                $reflector = new \ReflectionClass($definition);
                $property = $reflector->getProperty('class');
                $property->setAccessible(TRUE);
                $property->setValue($definition, $class);
                $definitions[] = $definition;
              }
            }
          }
        }
      }
    }
    return new PluginDefinitionSet(...$definitions);
  }

  /**
   * Extracts an array of class names from tokenized output of token_get_all().
   *
   * @param array $tokens
   *
   * @return string[]
   */
  protected function extractClassNames(array $tokens) : array {
    $classes = [];
    $namespace = $this->extractNamespace($tokens);
    foreach ($tokens as $id => $token) {
      if ($token[0] == T_CLASS) {
        $classes[] = $namespace ? $namespace . '\\' . $tokens[$id+2][1] : $tokens[$id+2][1];
      }
    }
    return $classes;
  }

  /**
   * Extracts a namespace from tokenized output of token_get_all().
   *
   * @param array $tokens
   *
   * @return string
   */
  protected function extractNamespace(array $tokens) : string {
    $namespace = '';
    $found = FALSE;
    foreach ($tokens as $token) {
      if (is_array($token) && $token[0] == T_NAMESPACE) {
        $found = TRUE;
      }
      if (is_array($token) && ($token[0] == T_NS_SEPARATOR || $token[0] == T_STRING)) {
        $namespace .= $token[1];
      }
      elseif ($found && $token == ';') {
        return $namespace;
      }
    }
    return $namespace;
  }

}