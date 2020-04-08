<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.ImageMeta
 *
 * @copyright   Copyright (C) NPEU 2019.
 * @license     MIT License; see LICENSE.md
 */

defined('_JEXEC') or die;

/**
 * Add and manage image metadata.
 */
class plgSystemImageMeta extends JPlugin
{
    protected $autoloadLanguage = true;

    /**
     * Add CSS and JS.
     */
    public function onBeforeRender()
    {
        $app = JFactory::getApplication();
        if (!$app->isAdmin()) {
            return; // Only run in admin
        }

        $option = $app->input->get('option');
        if ($option != 'com_media') {
            return; // Only run in com_media
        }

        // Only run this in the applicable folder:
        $folder = explode('/', $app->input->get('folder', '', 'path'));

        if ($this->params->get('folder') != '' && $this->params->get('folder') != $folder[0]) {
            return;
        }

        $dir = str_replace(JPATH_ROOT, '', __DIR__);

        $document = JFactory::getDocument();
        $document->addStyleSheet($dir . '/assets/vendor/webui-popover-1.2.19/jquery.webui-popover.min.css');
        $document->addStyleSheet($dir . '/assets/css/image-meta.css');

        $document->addScript($dir . '/assets/vendor/showdown-1.9.0/showdown.min.js');
        $document->addScript($dir . '/assets/vendor/webui-popover-1.2.19/jquery.webui-popover.min.js');
        $document->addScript($dir . '/assets/js/image-meta.js');
    }
}