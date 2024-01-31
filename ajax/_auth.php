<?php
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Event\Dispatcher as EventDispatcher;


#echo "<pre>\n"; var_dump($_GET); echo "</pre>\n";
#echo "<pre>\n"; var_dump($_SERVER); echo "</pre>\n";exit;
#echo "<pre>\n"; var_dump($_COOKIE); echo "</pre>\n";
switch ($_SERVER['SERVER_NAME']) {
    case 'dev.npeu.ox.ac.uk':
        $application_env = 'development';
        break;
    case 'test.npeu.ox.ac.uk':
    case 'sandbox.npeu.ox.ac.uk':
    case 'next.npeu.ox.ac.uk':
        $application_env = 'testing';
        break;
    default:
        $application_env = 'production';
}

$application_domain = str_replace('.npeu.ox.ac.uk', '', $_SERVER['SERVER_NAME']);

if ($application_env == 'development') {
    @define('DEV', true);
    ini_set('display_errors', 'on');
} else {
    @define('DEV', false);
}

if ($application_env == 'testing') {
    error_reporting(E_ALL ^ E_DEPRECATED);
    @define('TEST', true);
} else {
    @define('TEST', false);
}


$params = array();

// Set up Joomla User stuff:
define('DS', DIRECTORY_SEPARATOR);
$base_path = $_SERVER['DOCUMENT_ROOT'];
define('BASE_PATH', $base_path);
#echo "<pre>"; var_dump(BASE_PATH); echo "</pre>"; exit;
//define( 'JDATE', 'Y-m-d H:i:s A' );
//define( '_JEXEC', 1 );


switch ($application_domain) {
    case 'dev':
    case 'test':
    case 'sandbox':
    case 'next':
        //define( 'JPATH_BASE', BASE_PATH . 'jan_' . $application_domain . DS .'public' );
        define( 'TOP_DOMAIN', 'https://' . $_SERVER['SERVER_NAME']);
        define( 'JDB', 'jan_' . $application_domain);
        break;
    default:
        //define( 'JPATH_BASE', BASE_PATH . 'jan' . DS .'public' );
        define( 'TOP_DOMAIN', 'https://www.npeu.ox.ac.uk' );
        define( 'JDB', 'jan' );
}

define('_JEXEC', 1);

//If this file is not placed in the /root directory of a Joomla instance put the directory for Joomla libraries here.
$joomla_directory = BASE_PATH;

// From https://joomla.stackexchange.com/questions/33140/how-to-create-an-instance-of-the-joomla-cms-from-the-browser-or-the-command-line
// Via: https://joomla.stackexchange.com/questions/33389/standalone-php-script-to-get-username-in-joomla-4
/**---------------------------------------------------------------------------------
 * Part 1 - Load the Framework and set up up the environment properties
 * -------------------------------------------------------------------------------*/

/**
 *  Site - Front end application when called from Browser via URL.
*/                                                  // Remove this '*/' to comment out this block
define('JPATH_BASE', (isset($joomla_directory)) ? $joomla_directory : __DIR__ );
#echo "JPATH_BASE<pre>"; var_dump(JPATH_BASE); echo "</pre>"; exit;
require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';
$class_name             =  new \Joomla\CMS\Application\AdministratorApplication;
$session_alias          = 'session.web';
$session_suffix         = 'web.site';
/** end Site config */

/**---------------------------------------------------------------------------------
 * Part 2 - Start the application from the container ready to be used.
 * -------------------------------------------------------------------------------*/
// Boot the DI container
$container = \Joomla\CMS\Factory::getContainer();

// Alias the session service key to the web session service.
$container->alias($session_alias, 'session.' . $session_suffix)
          ->alias('JSession', 'session.' . $session_suffix)
          ->alias(\Joomla\CMS\Session\Session::class, 'session.' . $session_suffix)
          ->alias(\Joomla\Session\Session::class, 'session.' . $session_suffix)
          ->alias(\Joomla\Session\SessionInterface::class, 'session.' . $session_suffix);

// Instantiate the application.
$app = $container->get($class_name::class);
// Set the application as global app
\Joomla\CMS\Factory::$application = $app;

#echo "<pre>"; var_dump(get_class_methods($app)); echo "</pre>"; exit;

$session = Factory::getSession();
$user = Factory::getUser();
$user = Factory::getApplication()->getIdentity();

// Check if user has permission:
if (!$user->authorise('core.create', 'com_media')) {
    header('HTTP/1.0 403 Forbidden');
    die(JText::_('JGLOBAL_AUTH_ACCESS_DENIED'));
    return false;
}
// Allowed - continue: