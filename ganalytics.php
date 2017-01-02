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
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
            'onAssetsInitialized' => ['onAssetsInitialized', 0]
        ];
    }

    /**
     * Returns the Google Analytics cookie configuration.
     * @return string
     */
    private function getCookieConfiguration(){
        $cookie_config = $this->config->get('plugins.ganalytics.cookieConfig', false);
        if (!$cookie_config) return "'auto'";

        $cookie_config = [
          'cookieName'    => $this->config->get('plugins.ganalytics.cookieName', '_ga'),
          'cookieExpires' => $this->config->get('plugins.ganalytics.cookieExpires', 63072000),
        ];

        // cookie domain
        $cookie_domain = trim($this->config->get('plugins.ganalytics.cookieDomain'));
        if (!empty($cookie_domain)) $cookie_config['cookieDomain'] = $cookie_domain;

        return json_encode($cookie_config);
    }

    /**
     * Return the Google Analytics Tracking Code
     * @param string $scriptName Name of the GA script library
     * @param string $objectName Global variable name for the GA object
     * @param bool $async Determine if the GA script should be loaded and executed asynchronously
     * @return string
     */
    private function getTrackingCode($scriptName, $objectName, $async=false)
    {
        if ($async) {
            $code =
              "window.GoogleAnalyticsObject = '{$objectName}';\n".
              "window.{$objectName}=window.{$objectName}||function(){({$objectName}.q={$objectName}.q||[]).push(arguments)};{$objectName}.l=+new Date;\n"
            ;
        } else {
            $code =
                "(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){\n".
                "(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),\n".
                "m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)\n".
                "})(window,document,'script','//www.google-analytics.com/{$scriptName}.js','{$objectName}');\n"
            ;
        }
        return $code;
    }

    /**
     * Return all personalized GA settings
     * @param string $trackingId Google Analytics Tracking ID
     * @param string $objectName Global variable name for the GA object
     * @return array
     */
    private function getTrackingSettings($trackingId, $objectName)
    {
        $cookie_config = $this->getCookieConfiguration();

        $settings = [
          'trace-debug' =>  "window.ga_debug = {trace: true};",
          'create'      => "{$objectName}('create', '{$trackingId}', {$cookie_config});",
          'anonymize'   => "{$objectName}('set', 'anonymizeIp', true);",
          'force-ssl'   => "{$objectName}('set', 'forceSSL', true);",
          'send'        => "{$objectName}('send', 'pageview');"
        ];

        if (!$this->config->get('plugins.ganalytics.debugTrace', false)) unset ($settings['trace-debug']);
        if (!$this->config->get('plugins.ganalytics.anonymizeIp', false)) unset ($settings['anonymize']);
        if (!$this->config->get('plugins.ganalytics.forceSsl', false)) unset ($settings['force-ssl']);

        return $settings;
    }

    /**
     * Do something with deprecated settings
     */
    private function processDeprecatedSettings()
    {
        // 1.3.0 => 1.4.0
        // Field: "renameGa" => "objectName"
        $renameGa = trim($this->config->get('plugins.ganalytics.renameGa', ''));
        if (!empty($renameGa)) {
            $settings = $this->config->get('plugins.ganalytics', []);
            $settings['objectName'] = $renameGa;
            unset($settings['renameGa']);
            $this->config->set('plugins.ganalytics', $settings);
            Plugin::saveConfig('ganalytics');
        }
    }

    // Handle deprecated stuff when the plugin is initialized
    public function onPluginsInitialized()
    {
        $this->processDeprecatedSettings();
    }

    /**
     * Add GA tracking JS when the assets are initialized
     */
    public function onAssetsInitialized()
    {
        // Don't proceed if we are in the admin plugin
        if ($this->isAdmin()) return;

        // Don't proceed if there is no GA Tracking ID
        $trackingId = trim($this->config->get('plugins.ganalytics.trackingId', ''));
        if (empty($trackingId)) return;

        // Don't proceed if the IP address is blocked
        $blockedIps = $this->config->get('plugins.ganalytics.blockedIps', []);
        if (in_array($_SERVER['REMOTE_ADDR'], $blockedIps)) return;

        // Parameters
        $scriptName = $this->config->get('plugins.ganalytics.debugStatus', false) ? 'analytics_debug' : 'analytics';
        $objectName = trim($this->config->get('plugins.ganalytics.objectName', 'ga'));
        $async      = $this->config->get('plugins.ganalytics.async', false);
        $position   = trim($this->config->get('plugins.ganalytics.position', 'head'));

        // Tracking Code and settings
        $settings = $this->getTrackingSettings($trackingId, $objectName);
        $code = $this->getTrackingCode($scriptName, $objectName, $async);
        $code.= join(PHP_EOL, $settings);

        // Embed Google Analytics script
        $group = ($position == 'body') ? 'bottom' : null;

        $this->grav['assets']->addInlineJs($code, null, $group);
        if ($async) $this->grav['assets']->addJs("//www.google-analytics.com/{$scriptName}.js", 9 , true /*pipeline*/, 'async', $group);
    }
}
