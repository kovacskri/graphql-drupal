<?php

namespace Drupal\graphql_content_mutation\Plugin\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\graphql\Utility\StringHelper;
use Drupal\graphql_content_mutation\ContentEntityMutationSchemaConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EntityInputFieldDeriver extends DeriverBase implements ContainerDeriverInterface {
  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity field manager service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The schema configuration service.
   *
   * @var \Drupal\graphql_content_mutation\ContentEntityMutationSchemaConfig
   */
  protected $schemaConfig;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $basePluginId) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('graphql_content_mutation.schema_config')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    EntityFieldManagerInterface $entityFieldManager,
    ContentEntityMutationSchemaConfig $schemaConfig
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->entityFieldManager = $entityFieldManager;
    $this->schemaConfig = $schemaConfig;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    foreach ($this->entityTypeManager->getDefinitions() as $entityTypeId => $type) {
      if (!($type instanceof ContentEntityTypeInterface)) {
        continue;
      }

      if (!$this->schemaConfig->exposeAnyCreateOrUpdate($entityTypeId)) {
        continue;
      }

      foreach ($this->entityFieldManager->getFieldStorageDefinitions($entityTypeId) as $fieldName => $field) {
        $properties = [];
        $propertyDefinitions = $field->getPropertyDefinitions();

        // Skip this field input type if it's a single value field.
        if (count($propertyDefinitions) == 1 && array_keys($propertyDefinitions)[0] === $field->getMainPropertyName()) {
          continue;
        }

        foreach ($propertyDefinitions as $propertyName => $propertyDefinition) {
          if ($propertyDefinition->isReadOnly() || $propertyDefinition->isComputed()) {
            continue;
          }

          $properties[StringHelper::propCase($propertyName)] = [
            'type' => 'String',
            'nullable' => !$propertyDefinition->isRequired(),
            'multi' => $propertyDefinition->isList(),
            'property_name' => $propertyName,
          ];
        }

        $this->derivatives["$entityTypeId:$fieldName"] = [
          'name' => StringHelper::camelCase([$entityTypeId, $fieldName, 'field', 'input']),
          'fields' => $properties,
          'entity_type' => $entityTypeId,
          'field_name' => $fieldName,
        ] + $basePluginDefinition;
      }
    }

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

}
