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
     * @param string $scriptName Name of the GA script library
     * @param string $gaName Global variable name for the GA object
     * @param bool $async Determine if the GA script should be loaded and executed asynchronously
     * @return string
     */
    private function getTrackingCode($scriptName, $gaName, $async=false)
    {
        if ($async) {
            $code =
              "window.GoogleAnalyticsObject = '{$gaName}';\n".
              "window.{$gaName}=window.{$gaName}||function(){({$gaName}.q={$gaName}.q||[]).push(arguments)};{$gaName}.l=+new Date;\n"
            ;
        } else {
            $code =
                "(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){\n".
                "(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),\n".
                "m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)\n".
                "})(window,document,'script','//www.google-analytics.com/{$scriptName}.js','{$gaName}');\n"
            ;
        }
        return $code;
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
          'force-ssl'   => "{$gaName}('set', 'forceSSL', true);",
          'send'        => "{$gaName}('send', 'pageview');"
        ];

        if (!$this->config->get('plugins.ganalytics.debugTrace', false)) unset ($settings['trace-debug']);
        if (!$this->config->get('plugins.ganalytics.anonymizeIp', false)) unset ($settings['anonymize']);
        if (!$this->config->get('plugins.ganalytics.forceSsl', false)) unset ($settings['force-ssl']);

        return $settings;
    }

    /**
     * Add GA tracking JS when the assets are initialized
     */
    public function onAssetsInitialized()
    {
        // Don't proceed if we are in the admin plugin
        if ($this->isAdmin()) return;

        // Parameters
        $trackingId = trim($this->config->get('plugins.ganalytics.trackingId', ''));
        $position   = trim($this->config->get('plugins.ganalytics.position', 'head'));
        $scriptName = $this->config->get('plugins.ganalytics.debugStatus', false) ? 'analytics_debug' : 'analytics';
        $async      = $this->config->get('plugins.ganalytics.async', false);
        $blockedIps = $this->config->get('plugins.ganalytics.blockedIps', []);
        $gaName     = trim($this->config->get('plugins.ganalytics.renameGa', ''));

        // Don't proceed if there is no GA Tracking ID
        if (empty($trackingId)) return;

        // Don't proceed if the IP address is blocked
        if (in_array($_SERVER['REMOTE_ADDR'], $blockedIps)) return;

        // Set the global (ga) variable name
        if (empty($gaName)) $gaName = 'ga';

        // Tracking Code and settings
        $settings = $this->getTrackingSettings($trackingId, $gaName);
        $code = $this->getTrackingCode($scriptName, $gaName, $async);
        $code.= join(PHP_EOL, $settings);

        // Embed Goggle Analytics script
        $group = ($position == 'body') ? 'bottom' : null;

        $this->grav['assets']->addInlineJs($code, null, $group);
        if ($async) $this->grav['assets']->addJs("//www.google-analytics.com/{$scriptName}.js", 9 , true /*pipeline*/, 'async', $group);
    }
}
