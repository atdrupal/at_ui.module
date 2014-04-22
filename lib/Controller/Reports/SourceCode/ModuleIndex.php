<?php
namespace Drupal\at_ui\Controller\Reports\SourceCode;

class ModuleIndex {
  private $base_path = 'admin/reports/documentation/at_base/source';

  public function render() {
    foreach (system_list('module_enabled') as $module => $module_info) {
      $name = l($module, "{$this->base_path}/{$module}");
      $path = './' . drupal_get_path('module', $module);
      $rows[$module] = array($name, $path);
    }

    uksort($rows, function($a, $b) {
      return strnatcmp($a, $b);
    });

    return array(
      '#theme' => 'table',
      '#header' => array('Module', 'Directory'),
      '#rows' => $rows,
    );
  }
}
