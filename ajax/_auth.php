<?php
// No direct access
defined('_JEXEC') or die;

// Not sure it's possible to detect this, but I don't think it's ever likely to be different:
define('JPATH_BASE', realpath(dirname(dirname(dirname(dirname(__DIR__))))) . '/administrator');

require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

$app = JFactory::getApplication('administrator');
$app->initialise(null, false);

//// These don't appear to be needed but keep for reference:
#JPluginHelper::importPlugin('system');
#JPluginHelper::importPlugin('user');

#$dispatcher = JEventDispatcher::getInstance();
#$dispatcher->trigger('onAfterInitialise');

#$session = JFactory::getSession();
////

$user    = JFactory::getUser();

#echo '<pre>'; var_dump($user->groups); echo '</pre>';

if (!$user->authorise('core.create', 'com_media')) {

    header('HTTP/1.0 403 Forbidden');
    die(JText::_('JGLOBAL_AUTH_ACCESS_DENIED'));
    return false;
}

// Allowed - continue:
