<?php

namespace Drupal\at_ui\Form;

class TwigViewsForm
{

    public function get($form, $form_state)
    {
        $view_name = arg(3);

        $names = array('' => '-- Select a view --');
        foreach (views_get_all_views() as $name => $def) {
            $names[$def->base_table][$name] = $def->human_name . ' (' . $name . ')';
        }

        foreach ($names as &$_names) {
            if (is_array($_names)) {
                ksort($_names);
            }
        }

        $form['name'] = array(
            '#type'          => 'select',
            '#title'         => 'View name',
            '#options'       => $names,
            '#default_value' => $view_name,
        );

        if (!empty($view_name) && ($view = views_get_view($view_name))) {
            $this->getX($form, $view);
        }

        $form['submit'] = array(
            '#type'   => 'submit',
            '#value'  => 'Update',
            '#submit' => [[$this, 'submit']]
        );

        return $form;
    }

    public function submit($form, &$form_state)
    {
        $name = $form_state['values']['name'];
        $display_id = $form_state['values']['display_id'];
        drupal_goto("at/twig/views/{$name}/{$display_id}");
    }

    private function getX(&$form, $view)
    {
        $display_id = arg(4) ? arg(4) : 'default';
        $display_ids = array();

        foreach ($view->display as $name => $def) {
            $display_ids[$name] = $def->display_title;
        }

        $form['display_id'] = array(
            '#prefix'        => '<div id="at-ui-displays">',
            '#suffix'        => '</div>',
            '#type'          => 'select',
            '#title'         => 'Display ID',
            '#default_value' => $display_id,
            '#options'       => $display_ids,
        );

        if (!empty($display_id)) {
            $this->getPreview($form, $view, $display_id);
        }
    }

    private function getPreview(&$form, $view, $display_id)
    {
        $view->preview($display_id, array('all', 'all', 'all', 'all'));

        $fields = array(
            'row.item' => array(),
            'title', 'exposed', 'attachment_before', 'attachment_after', 'rows'
        );

        if ($view->result) {
            foreach (array_keys($view->result[0]) as $k) {
                $fields['row.item'][] = $k;
            }
        }

        $fields_markup = '<pre><code>' . print_r($fields, TRUE) . '</code></pre>';

        $form['fields'] = array(
            '#type'   => 'item',
            '#title'  => 'Available variables for view (Twig) template',
            '#markup' => $fields_markup,
        );
    }

}
