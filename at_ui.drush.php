<?php

/**
 * @file at_ui.drush.php
 */
require_once dirname(__FILE__) . '/includes/drush/at_services.php';
require_once dirname(__FILE__) . '/includes/drush/at_twig.php';

/**
 * Implements hook_drush_command()
 */
function at_ui_drush_command()
{
    $cmds = array();

    $cmds['at_service'] = array(
        'callback'    => 'drush_at_services',
        'description' => 'List all services',
        'examples'    => array(
            'drush at_service'             => 'List all services',
            'drush at_service server_name' => 'View definition of service_name.',
        ),
        'aliases'     => array('at-service', 'ats'),
        'bootstrap'   => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    );

    $cmds['at_doc_twig'] = array(
        'callback'    => 'drush_at_twig_doc',
        'description' => 'Lists twig functions, filters, globals and tests present in the current project',
        'examples'    => array(
            'drush twig:doc'            => 'Display all Twig functions/filters',
            'drush twig:doc drupalView' => 'View details of drupalView filter.'
        ),
        'aliases'     => array('twig:doc'),
    );

    return $cmds;
}
