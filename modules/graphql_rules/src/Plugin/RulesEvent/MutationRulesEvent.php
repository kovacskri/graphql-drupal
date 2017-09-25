<?php

namespace Drupal\graphql_rules\Plugin\RulesEvent;

use Drupal\Core\Form\FormStateInterface;
use Drupal\rules\EventHandler\ConfigurableEventHandlerBase;
use Symfony\Component\EventDispatcher\Event;

class MutationRulesEvent extends ConfigurableEventHandlerBase {

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [
      'module' => ['graphql_rules'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function determineQualifiedEvents(Event $event, $event_name, array &$event_definition) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    // No summary for now.
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    // No configuration for now.
  }

  /**
   * {@inheritdoc}
   */
  public function validate() {
    // Nothing to validate currently.
  }

  /**
   * {@inheritdoc}
   */
  public function getEventNameSuffix() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function refineContextDefinitions() {
    // TODO: Generate args and corresponding contexts from the configuration.
  }
}