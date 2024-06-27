<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.ImageMeta
 *
 * @copyright   Copyright (C) NPEU 2024.
 * @license     MIT License; see LICENSE.md
 */

namespace NPEU\Plugin\System\ImageMeta\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

/**
 * Add and manage image metadata.
 */
class ImageMeta extends CMSPlugin implements SubscriberInterface
{
    protected $autoloadLanguage = true;

    /**
     * An internal flag whether plugin should listen any event.
     *
     * @var bool
     *
     * @since   4.3.0
     */
    protected static $enabled = false;

    protected $modal = '';

    /**
     * Constructor
     *
     */
    public function __construct($subject, array $config = [], bool $enabled = true)
    {
        // The above enabled parameter was taken from teh Guided Tour plugin but it ir always seems
        // to be false so I'm not sure where this param is passed from. Overriding it for now.
        $enabled = true;


        #$this->loadLanguage();
        $this->autoloadLanguage = $enabled;
        self::$enabled          = $enabled;

        parent::__construct($subject, $config);
    }

    /**
     * function for getSubscribedEvents : new Joomla 4 feature
     *
     * @return array
     *
     * @since   4.3.0
     */
    public static function getSubscribedEvents(): array
    {
        return self::$enabled ? [
            'onBeforeRender' => 'onBeforeRender',
            'onAfterRender' => 'onAfterRender'
        ] : [];
    }

    /**
     * Add CSS and JS.
     */
    public function onBeforeRender(Event $event): void
    {
        $app = Factory::getApplication();
        if (!$app->isClient('administrator')) {
            return; // Only run in admin
        }

        $option = $app->input->get('option');
        if ($option != 'com_media') {
            return; // Only run in com_media
        }

        $path = $app->input->get('path', '', 'string');
        if (strpos($path, 'local-assets:/downloads/') === 0) {
            return; // Do not run in downloads folder
        }

        // Only run this in the applicable folder:
        $folder = explode('/', $app->input->get('folder', '', 'path'));

        if ($this->params->get('folder') != '' && $this->params->get('folder') != $folder[0]) {
            return;
        }

        $dir = str_replace(JPATH_ROOT, '', dirname(dirname(__DIR__)));

        $document = Factory::getDocument();
        //$document->addStyleSheet($dir . '/assets/vendor/webui-popover-1.2.19/jquery.webui-popover.min.css');
        $document->addStyleSheet($dir . '/assets/css/image-meta.css');

        $document->addScript($dir . '/assets/vendor/showdown-1.9.0/showdown.min.js');
        //$document->addScript($dir . '/assets/vendor/webui-popover-1.2.19/jquery.webui-popover.min.js');
        $document->addScript($dir . '/assets/js/image-meta.js');

        $modal_body = [
            '<div class="p-3">',
            '    <p><small>Accepts <a href="https://www.markdownguide.org/basic-syntax/" target="_blank">Markdown</a></small></p>',
            '    <p><textarea class="form-control  credit-input" rows="3" oninput="IMAGE_META.preview(this)"></textarea><input type="hidden" class="credit_for_image"></p>',
            '    <p>Preview:</p>',
            '    <div class="p-3 bg-light credit-preview"></div>',
            '</div>'
        ];
        /*
        'keyboard'    => false,
        'closeButton' => false,
        'bodyHeight'  => '70',
        'modalWidth'  => '80',
        */
        $this->modal = HTMLHelper::_(
            'bootstrap.renderModal',
            'imageMetaModal',
            [
                'title'       => 'Attribution (Credit line)',
                'backdrop'    => 'static',
                'closeButton' => false,
                'onhide'  => 'console.log("HIDE")',
                'footer'      => '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-bs-target="#closeBtn" onclick="IMAGE_META.close(this);">'. Text::_('JCANCEL') . '</button><button type="button" data-bs-dismiss="modal" data-bs-target="#closeBtn" class="btn btn-success" onclick="IMAGE_META.save(this);">'. Text::_('JAPPLY') . '</button>',
            ],
            implode("\n", $modal_body)
        );
    }

    /**
     * Replace strings in the body.
     */
    public function onAfterRender(Event $event): void
    {
        $app = Factory::getApplication();
        if (!$app->isClient('administrator')) {
            return; // Only run in admin
        }

        $option = $app->input->get('option');
        if ($option != 'com_media') {
            return; // Only run in com_media
        }

        // Get the response body.
        $body = $app->getBody();
        $app->setBody(str_replace('</body>', $this->modal . '</body>', $body));
    }
}