<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;

class GanalyticsPlugin extends Plugin
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'onAssetsInitialized' => ['onAssetsInitialized', 0]
        ];
    }

    /**
     * Add GA tracking JS
     */
    public function onAssetsInitialized()
    {
        if ($this->isAdmin()) {
            return;
        }

        $trackingId = trim($this->config->get('plugins.ganalytics.trackingId'));

        if ($trackingId) {
            $init = "
                window.ga=window.ga||function(){(ga.q=ga.q||[]).push(arguments)};ga.l=+new Date;
                ga('create', '{$trackingId}', 'auto');
                ga('send', 'pageview');
            ";
            $this->grav['assets']->addInlineJs($init);
            $this->grav['assets']->addAsyncJs('//www.google-analytics.com/analytics.js');
        }
    }
}
