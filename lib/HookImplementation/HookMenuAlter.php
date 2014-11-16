<?php

namespace Drupal\at_ui\HookImplementation;

class HookMenuAlter
{

    public function execute(array &$menu)
    {
        $this->alterDevelTabs($menu);
    }

    private function alterDevelTabs(&$menu)
    {
        $keys = array();
        $keys[] = 'user/%user/devel';
        $keys[] = 'node/%node/devel';
        $keys[] = 'comment/%comment/devel';
        $keys[] = 'taxonomy/term/%taxonomy_term/devel';
        if (isset($menu['block/%bean_delta/devel'])) {
            $keys[] = 'block/%bean_delta/devel';
        }

        foreach ($keys as $key) {
            $menu[$key]['page callback'] = $menu[$key . '/render']['page callback'] = 'atdr';
            $menu[$key]['page arguments'] = $menu[$key . '/render']['page arguments'] = [0 === strpos(arg(1, $key), '%') ? 1 : 2];
        }
    }

}
