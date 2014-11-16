<?php

namespace Drupal\at_ui\Controller\Reports;

class EntityTemplates
{

    public function render()
    {
        $rows = array();

        foreach (at_modules('at_base', 'entity_template') as $module) {
            $this->renderModule($module, $rows);
        }

        return array('#theme'  => 'table',
            '#header' => array(
                array('data' => 'Entity', 'width' => '100px'),
                array('data' => 'Bundle', 'width' => '100px'),
                array('data' => 'View Mode', 'width' => '100px'),
                array('data' => 'Config'),
                array('data' => 'Attached'),
                array('data' => 'Block'),
            ),
            '#rows'   => $rows,
            '#empty'  => 'Empty',
            '#suffix' => at_ui_tool_links());
    }

    private function renderModule($module, &$rows)
    {
        foreach (at_config($module, 'entity_template')->get('entity_templates') as $entity_type => $entity_config) {
            foreach ($entity_config as $bundle => $bundle_config) {
                $this->renderModuleEntity($entity_type, $bundle, $bundle_config, $rows);
            }
        }
    }

    private function renderModuleEntity($entity_type, $bundle, $bundle_config, &$rows)
    {
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
