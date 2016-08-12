<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;

/**
 * Class GanalyticsPlugin
 * @package Grav\Plugin
 */
class GanalyticsPlugin extends Plugin
{
    /**
     * @return array
     *
     * The getSubscribedEvents() gives the core a list of events
     *     that the plugin wants to listen to. The key of each
     *     array section is the event that the plugin listens to
     *     and the value (in the form of an array) contains the
     *     callable (or function) as well as the priority. The
     *     higher the number the higher the priority.
     */
    public static function getSubscribedEvents()
    {
        return [
            'onAssetsInitialized' => ['onAssetsInitialized', 0]
        ];
    }

    /**
     * Add GA tracking JS when the assets are initialized
     */
    public function onAssetsInitialized()
    {
        if ($this->isAdmin()) {
            return;
        }

        $trackingId = trim($this->config->get('plugins.ganalytics.trackingId'));

        if ($trackingId) {
            $script = $this->config->get('plugins.ganalytics.debugStatus') ? 'analytics_debug.js' : 'analytics.js';

            // Global (ga) Object
            $gaName = trim($this->config->get('plugins.ganalytics.renameGa'));
            if (!$gaName) $gaName = 'ga';

            $code =
                "(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){\n".
                "(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),\n".
                "m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)\n".
                "})(window,document,'script','//www.google-analytics.com/{$script}','{$gaName}');\n"
            ;

            $functions = [
                'trace-debug' =>  "window.ga_debug = {trace: true};",
                'create'      => "{$gaName}('create', '{$trackingId}', 'auto');",
                'anonymize'   => "{$gaName}('set', 'anonymizeIp', true);",
                'send'        => "{$gaName}('send', 'pageview');"
            ];

            if (!$this->config->get('plugins.ganalytics.debugTrace')) unset ($functions['trace-debug']);
            if (!$this->config->get('plugins.ganalytics.anonymizeIp')) unset ($functions['anonymize']);

            $code.= join(PHP_EOL, $functions);
            $this->grav['assets']->addInlineJs($code);
        }
    }
}
