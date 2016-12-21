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
     * Return the Google Analytics Tracking Code
     * @param string $gaName Global variable name for the GA object
     * @return string
     */
    private function getTrackingCode($gaName)
    {
        $script = $this->config->get('plugins.ganalytics.debugStatus', false) ? 'analytics_debug.js' : 'analytics.js';
        return
          "(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){\n".
          "(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),\n".
          "m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)\n".
          "})(window,document,'script','//www.google-analytics.com/{$script}','{$gaName}');\n\n"
        ;
    }

    /**
     * Return all personalized GA settings
     * @param string $trackingId Google Analytics Tracking ID
     * @param string $gaName Global variable name for the GA object
     * @return array
     */
    private function getTrackingSettings($trackingId, $gaName)
    {
        $settings = [
          'trace-debug' =>  "window.ga_debug = {trace: true};",
          'create'      => "{$gaName}('create', '{$trackingId}', 'auto');",
          'anonymize'   => "{$gaName}('set', 'anonymizeIp', true);",
          'send'        => "{$gaName}('send', 'pageview');"
        ];

        if (!$this->config->get('plugins.ganalytics.debugTrace', false)) unset ($settings['trace-debug']);
        if (!$this->config->get('plugins.ganalytics.anonymizeIp', false)) unset ($settings['anonymize']);

        return $settings;
    }

    /**
     * Add GA tracking JS when the assets are initialized
     */
    public function onAssetsInitialized()
    {
        if ($this->isAdmin()) return; // Return if we are in the Admin Plugin

        // Get the GA Tracking ID
        $trackingId = trim($this->config->get('plugins.ganalytics.trackingId', ''));
        if (empty($trackingId)) return;

        // Maybe the IP is blocked
        $blockedIps = $this->config->get('plugins.ganalytics.blockedIps', []);
        if (in_array($_SERVER['REMOTE_ADDR'], $blockedIps)) return;

        // Global (ga) variable
        $gaName = trim($this->config->get('plugins.ganalytics.renameGa', ''));
        if (empty($gaName)) $gaName = 'ga';

        // Tracking Code and settings
        $settings = $this->getTrackingSettings($trackingId, $gaName);
        $code = $this->getTrackingCode($gaName);
        $code.= join(PHP_EOL, $settings);

        $this->grav['assets']->addInlineJs($code);
    }
}
