<?php
namespace Drupal\at_ui\Controller\Reports;

class Entity_Templates {
  public function render() {
    $rows = array();

    foreach (at_modules('at_base', 'entity_template') as $module) {
      foreach (at_config($module, 'entity_template')->get('entity_templates') as $entity_type => $entity_config) {
        foreach ($entity_config as $bundle => $bundle_config) {
          foreach ($bundle_config as $view_mode => $config) {
            $attached = array();
            if (isset($config['attached'])) {
              $attached = $config['attached'];
              unset($config['attached']);
            }

            $blocks = array();
            if (isset($config['blocks'])) {
              $blocks = $config['blocks'];
              unset($config['blocks']);
            }

            $rows[] = array($entity_type, $bundle, $view_mode, atdr($config), atdr($attached), atdr($blocks));
          }
        }
      }
    }

    return array('#theme' => 'table',
      '#header' => array(
        array('data' => 'Entity', 'width' => '100px'),
        array('data' => 'Bundle', 'width' => '100px'),
        array('data' => 'View Mode', 'width' => '100px'),
        array('data' => 'Config'),
        array('data' => 'Attached'),
        array('data' => 'Block'),
      ),
      '#rows' => $rows,
      '#empty' => 'Empty');
  }
}
