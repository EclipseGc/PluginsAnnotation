<?php

namespace EclipseGc\PluginAnnotation\Discovery;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\DocParser;
use Doctrine\Common\Reflection\ClassFinderInterface;
use Doctrine\Common\Reflection\StaticReflectionParser;
use EclipseGc\Plugin\Discovery\PluginDefinitionSet;
use EclipseGc\Plugin\Discovery\PluginDiscoveryInterface;
use EclipseGc\Plugin\PluginDefinitionInterface;
use EclipseGc\PluginAnnotation\Exception\NonexistentAnnotationException;
use EclipseGc\PluginAnnotation\Exception\NonexistentInterfaceException;

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
   * @var \Doctrine\Common\Annotations\Reader
   */
  protected $reader;

  /**
   * AnnotatedPluginDiscovery constructor.
   *
   * @param \Traversable $namespaces
   *   A traversable list of namespaces.
   * @param string $directory
   *   The sub-directory structure plugins will appear in within namespaces.
   * @param string $interface
   *   The interface plugins must implement.
   * @param string $annotationClass
   *   The annotation class plugins must use.
   *
   * @throws \EclipseGc\PluginAnnotation\Exception\NonexistentAnnotationException
   * @throws \EclipseGc\PluginAnnotation\Exception\NonexistentInterfaceException
   *
   */
  public function __construct(\Traversable $namespaces, string $directory, string $interface, string $annotationClass) {
    if (!interface_exists($interface)) {
      throw new NonexistentInterfaceException(sprintf("The specified interface %s does not exist.", $interface));

    }
    if (!class_exists($annotationClass)) {
      throw new NonexistentAnnotationException(sprintf("The specified annotation class %s does not exist.", $annotationClass));
    }
    $this->namespaces = $namespaces;
    $this->directory = $directory;
    $this->interface = $interface;
    $this->annotationClass = $annotationClass;
  }

  /**
   * {@inheritdoc}
   */
  public function findPluginImplementations(PluginDefinitionInterface ...$definitions) : PluginDefinitionSet {
    // Clear the annotation loaders of any previous annotation classes.
    AnnotationRegistry::reset();
    // Register the namespaces of classes that can be used for annotations.
    AnnotationRegistry::registerLoader('class_exists');
    $reader = $this->getReader();
    foreach ($this->namespaces as $namespace => $directory) {
      $plugin_directory = "$directory/{$this->directory}";
      if (file_exists($plugin_directory)) {
        foreach (glob("$plugin_directory/*.php") as $file) {
          $file_contents = file_get_contents($file);
          $tokens = token_get_all($file_contents);
          $classes = $this->extractClassNames($tokens);
          foreach ($classes as $class) {
            if (class_exists($class) && !empty(class_implements($class)[$this->interface])) {
              $definition = $reader->getClassAnnotation((new StaticReflectionParser($class, $this->getFileFinder($file)))->getReflectionClass(), $this->annotationClass);
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

  /**
   * @param $file
   *
   * @return \Doctrine\Common\Reflection\ClassFinderInterface
   */
  protected function getFileFinder($file) {
    return new class($file) implements ClassFinderInterface {

      protected $file;

      public function __construct($file) {
        $this->file = $file;
      }

      public function findFile($class) {
        return $this->file;
      }
    };
  }

  /**
   * @return \Doctrine\Common\Annotations\Reader
   */
  protected function getReader() {
    if (empty($this->reader)) {
      $docParser = new DocParser();
      $docParser->setIgnoreNotImportedAnnotations(TRUE);
      $this->reader = new AnnotationReader($docParser);
    }
    return $this->reader;
  }

}
