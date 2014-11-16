<?php

namespace Drupal\at_ui\Controller;

class Icons
{

    public function renderServices()
    {
        $rows = array();

        foreach (at_container('container')->find('at.icon', 'service') as $name => $service) {
            foreach ($service->getIconSets() as $set_name) {
                $rows[] = array(l($service->getName(), "at/icon/{$name}"), l($set_name, "at/icon/{$name}/{$set_name}"));
            }
        }

        return array(
            '#theme'  => 'table',
            '#header' => array('Service', 'Set'),
            '#rows'   => $rows,
            '#suffix' => at_ui_tool_links());
    }

    /**
     * Callback for /at/icon/%/%
     *
     * @param  string $name     Service name.
     * @param  string $set_name
     * @return array
     */
    public function renderServiceSet($name, $set_name)
    {
        $service = at_container($name);
        $items = array();

        drupal_set_title("{$set_name} — " . $service->getName());

        foreach ($service->getIconList($set_name) as $icon_name) {
            $items[] = str_replace(
                '<i', '<i data-name="' . $icon_name . '"', $service->get("{$set_name}/{$icon_name}")->render()
            );
        }

        return array(
            '#theme'      => 'item_list',
            '#items'      => $items,
            '#attributes' => array(
                'class'        => array('at-icon-list'),
                'data-service' => $name,
                'data-set'     => $set_name,
            ),
        );
    }

}
