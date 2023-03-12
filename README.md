# Grav Google Analytics Plugin

The **Google Analytics** Plugin for [Grav CMS](http://github.com/getgrav/grav) allows you to integrate and configure [Google Analytics](https://www.google.com/analytics) without the need to touch any code within your Grav site.

### Features
* Preload the Google Analytics script asynchronously
* Choose the Google Analytics code position in the HTML document (head or body).
* Renaming of the Global (gtag) Object
* Debug Mode
* Custom Cookie Configuration. Name prefix, domain and expiration time are configurable.
* Blocking IP Addresses
* Opt Out (disable tracking by the user)
* Multi-Language Support for the [Grav Administration Panel](https://github.com/getgrav/grav-plugin-admin)

## Installation

Installing the Google Analytics plugin can be done in one of two ways. The GPM (Grav Package Manager) installation method enables you to quickly and easily install the plugin with a simple terminal command, while the manual method enables you to do so via a zip file.

### GPM Installation (Preferred)

The simplest way to install this plugin is via the [Grav Package Manager (GPM)](https://learn.getgrav.org/advanced/grav-gpm) through your system's terminal (also called the command line).  From the root of your Grav install type:

    bin/gpm install ganalytics

This will install the Google Analytics plugin into your `/user/plugins` directory within Grav. Its files can be found under `/your/site/grav/user/plugins/ganalytics`.

### Manual Installation

To install this plugin, just download the zip version of this repository and unzip it under `/your/site/grav/user/plugins`. Then, rename the folder to `ganalytics`. You can find these files on [GitHub](https://github.com/escopecz/grav-ganalytics) or via [GetGrav.org](https://getgrav.org/downloads/plugins).

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
objectName: "gtag"
blockedIps: []
blockedIpRanges: ["private", "loopback", "link-local"]
blockingCookie: "blockGA"

cookieConfig: false
cookiePrefix: ""
cookieDomain: ""
cookieExpires: 63072000

optOutEnabled: false
optOutMessage: "Google tracking is now disabled."

debugMode: false
```

* `enabled` Toggles if the Google Analytics plugin is turned on or off.
* `trackingId` The Google Analytics Tracking ID. This value is **required**.
_(You can also use environment variables by entering `env:VAR_NAME` as value)_
* `position` Code Position in the HTML document (`head` or `body`). Default is `head`.
* `objectName` The name for the global (gtag) object. Default is `gtag`.
* `blockedIps` Here you can blacklist IP addresses. For those the Google Analytics script will not be embedded.
* `blockedIpRanges` Here you can blacklist IPv4 and/or IPv6 address ranges in the form `["192.177.204.1-192.177.204.254", "2001:db8::1-2001:db8::fe", ...]`. In addition to numerical ranges, the keywords "private", "loopback", "link-local" designate special IPv4 and IPv6 ranges (see RFCs 6890, 4193, 4291). For blacklisted ranges the Google Analytics script will not be embedded. By default, all three ranges are blocked. If you are using a reverse proxy that redirects traffic to the grav installation, you may need to remove "private".
* `blockingCookie` The name of a blocking cookie. When such a cookie is set, the Google Analytics script will not be embedded. Default ist `blockGA`

* `cookieConfig`: Toggles if the a custom cookie configuration should be used.
* `cookiePrefix` The cookie name prefix.
* `cookieDomain`  The cookie domain. Optional, Google default is top level domain plus one subdomain (eTLD +1). For example Grav site under https://example.com would use example.com for the cookie domain, and https://subdomain.example.com would also use example.com for the cookie domain.
* `cookieExpires` The cookie expiration time in seconds. Google default is 2 years (`63072000` seconds)

* `optOutEnabled` Toggles if opt out function is turned on or off.
* `optOutMessage` Confirmation message shown to the user when opt out function is called

* `debugMode` Toggles if Google Analytics debug mode is enabled or disabled.

## Usage

1. Sign in to your [Google Analytics account](https://www.google.com/analytics/web/#home).
2. Select the **Admin** tab.
3. Select an account from the dropdown in the _ACCOUNT_ column.
4. Select a property from the dropdown in the _PROPERTY_ column.
5. Under _PROPERTY_, click **Tracking Info > Tracking Code**.
6. Copy the **Tracking ID** (a string like _G-XXXXXXXXXX_)
7. Add it to the configuration of this plugin.

To give your users the possibility to disable Google Analytics tracking you have to enable "opt out" in this plugin and put the following link somewhere in your pages, e.g. in your Privacy Declaration:

```html
<a href="javascript:gaOptout()">Disable Google Analytics</a>
```

The link must be inserted as HTML tags and not in markdown syntax. 
When this link is clicked, then the official ga-disable-cookie is set and Google stopps tracking this visitor.
For more Info about disabling the Google Analytics tracking see: https://developers.google.com/analytics/devguides/collection/gajs/#disable
