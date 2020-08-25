# Grav Google Analytics Plugin

The **Google Analytics** Plugin for [Grav CMS](http://github.com/getgrav/grav) allows you to integrate and configure [Google Analytics](https://www.google.com/analytics) without the need to touch any code within your Grav site.

### Features
* Preload the Google Analytics script asynchronously
* IP Anonymization
* Choose the Google Analytics code position in the HTML document (head or body).
* Force SSL (HTTPS). Send all data using SSL, even from insecure (HTTP) pages.
* Renaming of the Global (ga) Object
* Debug Mode with Trace Debugging
* Custom Cookie Configuration. Name, domain and expiration time are configurable.
* Blocking IP Addresses
* Opt Out (disable tracking by the user or external cookie)
* Opt In (explicitly allow tracking by external cookie)
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
objectName: "ga"
forceSsl: true
async: false
anonymizeIp: true
blockedIps: []
blockedIpRanges: ["private", "loopback", "link-local"]
blockingCookie: "blockGA"
blockingCookieAllowValue: ""

cookieConfig: false
cookieName: "_ga"
cookieDomain: ""
cookieExpires: 63072000

optOutEnabled: false
optOutMessage: "Google tracking is now disabled."
optOutRevertMessage: "Google tracking is now enabled."

debugStatus: false
debugTrace: false
```

* `enabled` Toggles if the Google Analytics plugin is turned on or off.
* `trackingId` The Google Analytics Tracking ID. This value is **required**.
_(You can also use environment variables by entering `env:VAR_NAME` as value)_
* `position` Code Position in the HTML document (`head` or `body`). Default is `head`.
* `async` Toggles if the Google Analytics script is preloaded asynchronously.
* `forceSsl` Toggles if Google Analytics should send all data using HTTPS.
* `objectName` The name for the global (ga) object. Default is `ga`.
* `anonymizeIp` Toggles if Google Analytics will anonymize the IP address for all hits.
* `blockedIps` Here you can blacklist IP addresses. For those the Google Analytics script will not be embedded.
* `blockedIpRanges` Here you can blacklist IPv4 and/or IPv6 address ranges in the form `["192.177.204.1-192.177.204.254", "2001:db8::1-2001:db8::fe", ...]`. In addition to numerical ranges, the keywords "private", "loopback", "link-local" designate special IPv4 and IPv6 ranges (see RFCs 6890, 4193, 4291). For blacklisted ranges the Google Analytics script will not be embedded. By default, all three ranges are blocked. If you are using a reverse proxy that redirects traffic to the grav installation, you may need to remove "private".
* `blockingCookie` The name of a blocking cookie. When such a cookie is set, the Google Analytics script will not be embedded. Default ist `blockGA`
* `blockingCookieAllowValue` The value of the blocking cookie to explicitly allow tracking. Only when the blocking cookie is set to this value, the Google Analytics script will be embedded. Default is `` (not set).

* `cookieConfig`: Toggles if the a custom cookie configuration should be used.
* `cookieName` The cookie name. Default ist `_ga`
* `cookieDomain`  The cookie domain.
* `cookieExpires` The cookie expiration time in seconds. Google default is 2 years (`63072000` seconds)

* `optOutEnabled` Toggles if opt out JavaScript functions are turned on or off.
* `optOutMessage` Confirmation message shown to the user when opt out function is called.
* `optOutRevertMessage` Confirmation message shown to the user when opt out function is reverted and tracking is enabled again.

* `debugStatus` Toggles if the debug version of Google Analytics is enabled or disabled.
* `debugTrace` Toggles if the debugger will output more verbose information to the console. `debugStatus` must be enabled.

## Usage

1. Sign in to your [Google Analytics account](https://www.google.com/analytics/web/#home).
2. Select the **Admin** tab.
3. Select an account from the dropdown in the _ACCOUNT_ column.
4. Select a property from the dropdown in the _PROPERTY_ column.
5. Under _PROPERTY_, click **Tracking Info > Tracking Code**.
6. Copy the **Tracking ID** (a string like _UA-000000-01_)
7. Add it to the configuration of this plugin.

### Disable tracking
There are three ways to give your users the possibility to disable Google Analytics tracking:
+ Opt-In by external cookie
+ Opt-Out by external cookie
+ Opt-Out by JavaScript function call
 
#### Opt-In by Cookie
You may use a cookie consent function or plugin to set a blocking cookie to explicitly allow tracking:
```
blockingCookie: "{name of the cookie}"
blockingCookieAllowValue: "{value of this cookie to allow tracking}"
```
By this configuration the Google Analytics Tracking code is inserted into the page, only when the blocking cookie with this value is found. 
In this way the first page view is never tracked because tracking starts after the consent is given by setting the cookie (opt-in).

Example: The configuration for "opt-in" using the cookie consent plugin https://github.com/naucon/grav-plugin-cookieconsent with compliance_type "opt-in" (v0.5.0) is: 
```
blockingCookie: "cookieconsent_status"
blockingCookieAllowValue: "allow"
```

#### Opt-Out by Cookie
You may use a cookie consent function or plugin to set a blocking cookie to disable tracking:
```
blockingCookie: "{name of the cookie}"
blockingCookieAllowValue: "" {empty}
```
By this configuration the Google Analytics Tracking code is removed from the page, when the blocking cookie is found, regardless of its value. 
In this way the first page view always is tracked because tracking stops after the cookie is set (opt-out).

#### Opt-Out by JavaScript
You have to enable "opt out" in this plugin and put the following link somewhere in your pages, e.g. in your Privacy Declaration:

```html
<a href="javascript:gaOptout()">Disable Google Analytics</a>
```

The link must be inserted as HTML tags and not in markdown syntax. 
When this link is clicked, then the official ga-disable-cookie is set and Google stops tracking this visitor.
For more Info about disabling the Google Analytics tracking see: https://developers.google.com/analytics/devguides/collection/gajs/#disable

You may also set or delete the official ga-disable-cookie by the JavaScript function `setGaTracking(on)`. See the following code example of a checkbox to show and  disable or enable Google Analytics Tracking:

```html
<label><input type=checkbox id="gaCheckbox" name="gaCheckbox" value="1" onClick="changeGaTracking()"> Google Analytics Tracking.</label> 
<script>
var checkBox = document.getElementById("gaCheckbox");
checkBox.checked = getGaTracking(); // show tracking status
function changeGaTracking() {
  setGaTracking(checkBox.checked); // change tracking status
}
</script>
```

You may also use the JavaScript function `setGaTracking(on)` as a callback function in a cookie consent plugin.
