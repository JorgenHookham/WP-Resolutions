# WP Resolutions #

Contributors: JorgenScott

Tags: responsive design, adaptive, adaptive-images

Requires at least: 3.3

Tested up to: 3.3.1

Stable tag: 1.0

Adaptive-Images for WordPress: make sure that image files are never bigger than what a device can use.

## Description ##

Resolutions will make sure that image files are never bigger than what a device can use, so the 4000 x 4000 photos that your client or content team post won’t melt any smartphones.

It is a one-click WordPress implementation of Matt Wilcox’s Adaptive-Images plugin. See [adaptive-images.com](http://adaptive-images.com) for more info.

## Features ##

* Up and running in seconds.
* Protects your website from misuse by content creators, clients, etc.
* Makes images on your site load faster.
* Saves bandwidth for everyone.

## How It Works ##
When a device lands on a resolutions-enabled site, JavaScript creates a cookie that stores the device’s resolution. Then, `.htaccess` redirects image requests to `adaptive-images.php`, which reads the cookie and creates/caches appropriately-sized versions of images before sending them to the viewer.

## Installation ##

1. Install and activate the plugin from your WordPress dashboard.
2. Resolutions is now watching all of the image files in your uploads directory.
