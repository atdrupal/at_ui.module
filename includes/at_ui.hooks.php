<?php

use Drupal\at_ui\HookImplementation\HookMenuAlter;

/**
 * Implements hook_init()
 */
function at_ui_init()
{
    if (at_debug() && !defined('AT_DISABLE_DEVELOPMENT_INDICATOR') && empty($_GET['no-indicator'])) {
        drupal_add_css(drupal_get_path('module', 'at_ui') . '/misc/css/dev.indicator.css');
        drupal_add_js(drupal_get_path('module', 'at_ui') . '/misc/js/dev.indicator.js');
    }
}

/**
 * Implements hook_admin_paths()
 */
function at_ui_admin_paths()
{
    return array('at/*' => TRUE);
}

/**
 * Implements hook_menu()
 */
function at_ui_menu_alter(&$menu)
{
    $obj = new \Drupal\at_ui\HookImplementation\HookMenuAlter();
    $obj->execute($menu);
}

/**
 * Implements hook_theme_registry_alter().
 * Move user_admin_permissions theme's processing to new place.
 *
 * @param array $theme_registry
 */
function at_ui_theme_registry_alter(&$theme_registry)
{
    $theme_registry['user_admin_permissions']['theme path'] = drupal_get_path('module', 'at_ui');
    $theme_registry['user_admin_permissions']['function'] = 'at_ui_user_admin_permissions';
    unset($theme_registry['user_admin_permissions']['includes']);
}

/**
 * Implements hook_form_FORM_ID_alter().
 */
function at_ui_form_simpletest_test_form_alter(&$form, $form_state)
{
    drupal_add_js(drupal_get_path('module', 'at_ui') . '/misc/js/at.simpletest.landing.js');
}
