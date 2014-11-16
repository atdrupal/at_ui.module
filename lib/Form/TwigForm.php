<?php

namespace Drupal\at_ui\Form;

class TwigForm
{

    public function get($form, $form_state)
    {
        $form += array(
            '#cache'    => FALSE,
            '#redirect' => FALSE,
            '#suffix'   => at_ui_tool_links(),
        );

        $form['code'] = array(
            '#type'          => 'textarea',
            '#resizable'     => FALSE,
            '#default_value' => (isset($_SESSION['twig_execute_code']) ? $_SESSION['twig_execute_code'] : "{{ user.access|fn__format_date('long') }}"),
            '#description'   => at_ui_codemirror_submit_shortcut_hint(),
            '#ajax'          => array(
                'event'    => 'change',
                'callback' => 'at_ui_twig_form_ajax_callback',
                'wrapper'  => 'at-ui-results',
            ),
        );

        $form['result'] = array(
            '#prefix' => '<div id="at-ui-results">',
            '#suffix' => '</div>',
            '#markup' => '',
        );

        $form['submit'] = array('#type' => 'submit', '#value' => 'Flush compiled Twig templates');

        return $form;
    }

    /**
     * Flush compiled Twig templates.
     */
    public function submit()
    {
        foreach (file_scan_directory(drupal_realpath(variable_get('file_temporary_path')) . '/', '/\.php$/') as $file) {
            unlink($file->uri);
        }
        drupal_set_messagse(t('Flushed compiled Twig templates.'));
    }

    /**
     * Ajax submit for Twig form.
     */
    public function ajaxSubmit($form, &$form_state)
    {
        $_SESSION['twig_execute_code'] = $code = $form_state['values']['code'];

        ob_start();
        try {
            print at_container('twig_string')->render($code);
        }
        catch (Exception $e) {
            drupal_set_message($e->getMessage());
            at_trace($e->getTrace(), 'message');
        }

        $output = ob_get_clean();

        // Render
        $form['result']['#markup'] = atdr($output);

        return $form['result'];
    }

}
