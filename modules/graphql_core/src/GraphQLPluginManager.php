<?php

namespace Drupal\graphql_core;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\graphql_core\GraphQLPluginInterface;
use Drupal\graphql_core\GraphQLSchemaManager;
use Psr\Log\LoggerInterface;
use Traversable;

/**
 * Base class for GraphQL Plugin managers.
 */
class GraphQLPluginManager extends DefaultPluginManager {

  /**
   * Static cache for plugin instances.
   *
   * @var object[]
   */
  protected $instances = [];

  /**
   * An instance of the GraphQL schema manager to pull dependencies.
   *
   * @var \Drupal\graphql_core\GraphQLSchemaManager
   *   Reference to the GraphQL schema manager.
   */
  protected $schemaManager;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    $pluginSubdirectory,
    Traversable $namespaces,
    ModuleHandlerInterface $moduleHandler,
    $pluginInterface,
    $pluginAnnotationName,
    GraphQLSchemaManager $schemaManager,
    $alterInfo,
    LoggerInterface $logger
  ) {
    $this->schemaManager = $schemaManager;
    $this->alterInfo($alterInfo);
    $this->logger = $logger;
    parent::__construct(
      $pluginSubdirectory,
      $namespaces,
      $moduleHandler,
      $pluginInterface,
      $pluginAnnotationName
    );
  }

  /**
   * {@inheritdoc}
   */
  public function createInstance($pluginId, array $configuration = []) {
    if (!array_key_exists($pluginId, $this->instances)) {
      // We deliberately ignore that $configuration could be different, because
      // GraphQL plugins don't contain user defined configuration.
      $this->instances[$pluginId] = parent::createInstance($pluginId);
      if ($this->instances[$pluginId] instanceof GraphQLPluginInterface) {
        try {
          $this->instances[$pluginId]->buildConfig($this->schemaManager);
        }
        catch (\Exception $exception) {
          $this->logger->warning('Plugin ' . $pluginId . ' could not be added to the GraphQL schema: ' . $exception->getMessage());
          $this->instances[$pluginId] = NULL;
        }
      }
    }
    return $this->instances[$pluginId];
  }

}
