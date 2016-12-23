# Grav Google Analytics Plugin

The **Google Analytics** Plugin for [Grav CMS](http://github.com/getgrav/grav) allows you to integrate and configure [Google Analytics](https://www.google.com/analytics) without the need to touch any code within your Grav site.

### Features
* Preload the Google Analytics script asynchronously
* IP Anonymization
* Choose the Google Analytics code position in the HTML document (head or body).
* Force SSL (HTTPS). Send all data using SSL, even from insecure (HTTP) pages.
* Renaming of the Global (ga) Object
* Debug Mode with Trace Debugging
* Blocking IP Addresses
* Multi-Language Support for the [Grav Administration Panel](https://github.com/getgrav/grav-plugin-admin)

## Installation

Installing the Google Analytics plugin can be done in one of two ways. The GPM (Grav Package Manager) installation method enables you to quickly and easily install the plugin with a simple terminal command, while the manual method enables you to do so via a zip file.

### GPM Installation (Preferred)

The simplest way to install this plugin is via the [Grav Package Manager (GPM)](http://learn.getgrav.org/advanced/grav-gpm) through your system's terminal (also called the command line).  From the root of your Grav install type:

    bin/gpm install ganalytics

This will install the Google Analytics plugin into your `/user/plugins` directory within Grav. Its files can be found under `/your/site/grav/user/plugins/ganalytics`.

### Manual Installation

To install this plugin, just download the zip version of this repository and unzip it under `/your/site/grav/user/plugins`. Then, rename the folder to `ganalytics`. You can find these files on [GitHub](https://github.com/escopecz/grav-ganalytics) or via [GetGrav.org](http://getgrav.org/downloads/plugins).

You should now have all the plugin files under

    /your/site/grav/user/plugins/ganalytics
	
> NOTE: This plugin is a modular component for Grav which requires [Grav](http://github.com/getgrav/grav) to operate.

## Configuration

Before configuring this plugin, you should copy the `user/plugins/ganalytics/ganalytics.yaml` to `user/config/plugins/ganalytics.yaml` and only edit that copy.

Here is the default configuration and an explanation of available options:

```yaml
enabled: true 
trackingId: ""
position: "head"
renameGa: ""
async: false
forceSsl: false
anonymizeIp: false
blockedIps: []
debugStatus: false
debugTrace: false
```

* `enabled` Toggles if the Google Analytics plugin is turned on or off.
* `trackingId` The Google Analytics Tracking ID. This value is **required**.
* `position` Code Position in the HTML document (head or body).
* `async` Toggles if the Google Analytics script is preloaded asynchronously.
* `forceSsl` Toggles if Google Analytics should send all data using HTTPS.
* `renameGa` In some cases you'd like to add Google Analytics to your page, but the `ga` variable is already being used for something else. To deal with this, you can rename the global ga object.
* `anonymizeIp` Toggles if Google Analytics will anonymize the IP address for all hits.
* `blockedIps` Here you can blacklist IP addresses. For those the Google Analytics script will not be embedded.
* `debugStatus` Toggles if the debug version of Goggle Analytics is enabled or disabled.
* `debugTrace` Toggles if the debugger will output more verbose information to the console. `debugStatus` must be enabled.

## Usage

1. Sign in to your [Google Analytics account](https://www.google.com/analytics/web/#home).
2. Select the **Admin** tab.
3. Select an account from the dropdown in the _ACCOUNT_ column.
4. Select a property from the dropdown in the _PROPERTY_ column.
5. Under _PROPERTY_, click **Tracking Info > Tracking Code**.
6. Copy the **Tracking ID** (a string like _UA-000000-01_)
7. Add it to the configuration of this plugin.
