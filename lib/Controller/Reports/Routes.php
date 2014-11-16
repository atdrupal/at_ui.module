<?php

namespace Drupal\at_ui\Controller\Reports;

class Routes
{

    public function render()
    {
        $rows = array();
        foreach (at_modules('at_base', 'routes') as $module) {
            foreach (at_config($module, 'routes')->get('routes') as $path => $route) {
                $attached = array();
                if (isset($route['attached'])) {
                    $attached = $route['attached'];
                    unset($route['attached']);
                }

                $blocks = array();
                if (isset($route['blocks'])) {
                    $blocks = $route['blocks'];
                    unset($route['blocks']);
                }

                $breadcrumbs = array();
                if (isset($route['breadcrumbs'])) {
                    $breadcrumbs = $route['breadcrumbs'];
                    unset($route['breadcrumbs']);
                }
                $breadcrumbs = array_merge($breadcrumbs, $this->findExternalBreadcrumbs($path));

                $rows[] = array($module, $path, atdr($route), atdr($attached), atdr($blocks), atdr($breadcrumbs));
            }
        }

        return array('#theme'  => 'table',
            '#header' => array(
                array('data' => 'Module', 'width' => '100px'),
                array('data' => 'Path', 'width' => '100px'),
                'Route',
                'Attached',
                'Blocks',
                'Breadcrumb',
            ),
            '#rows'   => $rows,
            '#suffix' => at_ui_tool_links()
        );
    }

    public function findExternalBreadcrumbs($path)
    {
        foreach (at_modules('at_base', 'breadcrumb') as $module) {
            $config = at_config($module, 'breadcrumb')->get('breadcrumb');
            if (isset($config['paths'][$path])) {
                return $config['paths'][$path];
            }
        }
        return array();
    }

}
