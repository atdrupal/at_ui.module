<?php

use Drupal\at_ui\Form\SQLForm;
use Drupal\at_ui\Form\TwigForm;
use Drupal\at_ui\Form\TwigViewsForm;

function at_ui_twig_form($form, $form_state)
{
    $obj = new TwigForm();
    return $obj->get($form, $form_state);
}

/**
 * Ajax callback for at_ui_twig_form().
 */
function at_ui_twig_form_ajax_callback($form, &$form_state)
{
    $obj = new TwigForm();
    return $obj->ajaxSubmit($form, $form_state);
}

/**
 * Submit callback for at_ui_twig_form().
 */
function at_ui_twig_form_submit($form, &$form_state)
{
    $obj = new TwigForm();
    return $obj->submit($form, $form_state);
}

/**
 * Define form structure for /at/twig/views
 */
function at_ui_twig_views_form($form = [], $form_state)
{
    $obj = new TwigViewsForm();
    return $obj->get($form, $form_state);
}

/**
 * Form for /at/sql.
 */
function at_ui_sql_form($form, &$form_state)
{
    $obj = new SQLForm();
    return $obj->get($form, $form_state);
}

/**
 * Handler for ajax submit.
 */
function at_ui_sql_form_ajax_callback($form, &$form_state)
{
    $obj = new SQLForm();
    return $obj->submit($form, $form_state);
}
