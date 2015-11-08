# Google Analytics Grav Plugin

This is [Grav CMS](http://getgrav.org) plugin that helps you implement [Google Analytics](https://www.google.com/analytics) tracking code into your website. That way your GA tracking will be theme-independent.

# Installation

Installing the Google Analytics plugin can be done in one of two ways.

## GPM Installation (Preferred)

The simplest way to install this plugin is via the [Grav Package Manager (GPM)](http://learn.getgrav.org/advanced/grav-gpm) through your system's Terminal (also called the command line).  From the root of your Grav install type:

    bin/gpm install ganalytics

This will install the Google Analytics plugin into your `/user/plugins` directory within Grav. Its files can be found under `/your/site/grav/user/plugins/ganalytics`.

## Manual Installation

To install this plugin, just [download](https://github.com/escopecz/grav-ganalytics/archive/master.zip) the zip version of this repository and unzip it under `/your/site/grav/user/plugins`. Then, rename the folder to `ganalytics`.

You should now have all the plugin files under

    /your/site/grav/user/plugins/ganalytics

# Config Defaults

```
enabled: true
trackingId: ''
```

If you need to change any value, then the best process is to copy the [ganalytics.yaml](ganalytics.yaml) file into your `users/config/plugins/` folder (create it if it doesn't exist), and then modify there. This will override the default settings.

# Usage

1. In your Google Analytics account, open the analytics of the Grav website (or create new if doesn't exist yet).
2. Go to *Admin* / *Tracking Info* / *Tracking Code*
3. Copy the *Tracking ID* and insert it to the configuration of this plugin.
