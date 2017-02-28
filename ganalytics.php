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
                "})(window,document,'script','https://www.google-analytics.com/{$scriptName}.js','{$objectName}');\n"
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
     * Returns a canonical IP4 address which can be string-compared to another canonical IP address
     * @param string $originalIPAddress IP4 adress string in decimal dot notation (e.g. '127.0.0.1')
     * @return string (e.g. '127.000.000.001')
     */
    private static function canonicalIPAddress($originalIPAddress)
    {
        $adressBytes = array_merge(explode('.', $originalIPAddress), ['0', '0', '0', '0']);
        return sprintf('%03s.%03s.%03s.%03s', $adressBytes[0], $adressBytes[1], $adressBytes[2], $adressBytes[3]);
    }

    /**
     * Returns TRUE, if a canonical IP address is within the specified address range
     * @param string $canonicalAddress
     * @param string $range
     * @return boolean
     */
    private function inIPAdressRange($canonicalAddress, $range)
    {
        if ($range === 'private') {  // RFC 6890
            return ($this->inIPAdressRange($canonicalAddress, "10.0.0.0-10.255.255.255")
                || $this->inIPAdressRange($canonicalAddress, "172.16.0.0-172.31.255.255")
                || $this->inIPAdressRange($canonicalAddress, "192.168.0.0-192.168.255.255"));
        } elseif ($range === 'loopback') {  // RFC 6890
            return ($this->inIPAdressRange($canonicalAddress, "127.0.0.0-127.255.255.255"));
        } elseif ($range === 'link-local') {  // RFC 6890
            return ($this->inIPAdressRange($canonicalAddress, "169.254.0.0-169.254.255.255"));
        } else {
            $rangeLimits = explode('-', $range);
            if (count($rangeLimits) == 2
                && strcmp($canonicalAddress, $this->canonicalIPAddress($rangeLimits[0])) >= 0
                && strcmp($canonicalAddress, $this->canonicalIPAddress($rangeLimits[1])) <= 0) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Documents the reason for blocking GA tracking in a JavaScript comment
     * @param string $reason
     */
    private function documentBlockingReason($reason)
    {
        $this->grav['assets']->addInlineJs("/* GA tracking blocked, reason: $reason */");
    }

    /**
     * Add GA tracking JS when the assets are initialized
     */
    public function onAssetsInitialized()
    {
        // Don't proceed if we are in the admin plugin
        if ($this->isAdmin()) {
            $this->documentBlockingReason('admin plugin active');
            return;
        }

        // Don't proceed if there is no GA Tracking ID
        $trackingId = trim($this->config->get('plugins.ganalytics.trackingId', ''));
        if (empty($trackingId)) {
            $this->documentBlockingReason('trackingId not configured');
            return;
        }

        // Don't proceed if a blocking cookie is set
        $blockingCookieName = $this->config->get('plugins.ganalytics.blockingCookie', '');
        if (!empty($blockingCookieName) && !empty($_COOKIE[$blockingCookieName])) {
            $this->documentBlockingReason("blocking cookie \"$blockingCookieName\" is set");
            return;
        }

        // Don't proceed if the IP address is blocked
        $blockedIps = $this->config->get('plugins.ganalytics.blockedIps', []);
        if (in_array($_SERVER['REMOTE_ADDR'], $blockedIps)) {
            $this->documentBlockingReason("client ip " . $_SERVER['REMOTE_ADDR'] . " is in blockedIps");
            return;
        }

        // Don't proceed if the IP address is within a blocked range
        $canonicalClientIpAddress = $this->canonicalIPAddress($_SERVER['REMOTE_ADDR']);
        $blockedIpRanges = $this->config->get('plugins.ganalytics.blockedIpRanges', []);
        foreach ($blockedIpRanges as $blockedIpRange) {
            if ($this->inIPAdressRange($canonicalClientIpAddress, $blockedIpRange)) {
                $this->documentBlockingReason("client ip " . $_SERVER['REMOTE_ADDR'] . " is in range \"" . $blockedIpRange . "\"");
                return;
            }
        }

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
