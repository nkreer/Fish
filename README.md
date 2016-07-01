# Fish 

![TravisCI build](https://travis-ci.org/nkreer/Fish.svg)
![License](https://img.shields.io/badge/License-GPL%20v3-blue.svg)
![Version](https://img.shields.io/badge/Latest%20Version-1.0-lightgrey.svg)

Fish is an IRC-Bot with a powerful plugin API written in PHP. 
It is distributed under the terms and conditions of the [GPL Version 3 License](LICENSE).

## Installation

Fish has been tested to work well on unix systems and PHP 7.
It does not work with PHP5 or Windows, and compatibility with the HHVM is not guaranteed. 
Some small parts of this software require the bash shell.

To install Fish, make sure you have [composer](https://getcomposer.org) installed. 
For the best compatibility with plugins, you should always use [the latest release](https://github.com/nkreer/Fish/releases). 
Just download the source code, extract it, navigate there and run:

```$ composer install```

## Features

Fish offers many features for interaction with an IRC Server out of the box: 

* Super simple and powerful plugin API (The most simple you will find in a PHP IRC-Bot to date!)
* Support for multiple connections in one process
* Built-in and adapting management-commands (join, part, help, etc.)
* Built-in user authentication features
* Built-in permission management
* Many more

## Extend

Several plugins have already been written for Fish: 

| Plugin | Description |
| ------ | ----------- |
|[PluginTools](https://github.com/nkreer/PluginTools)| Helps you with the packaging of your plugins |
|[Permissions](https://github.com/nkreer/Permissions)| Changes users' permissions on IRC |
|[Scripts](https://github.com/nkreer/Fish-Scripts)| Enables you to add simple custom commands to the bot |

If you want me to add your plugin to the list, open a pull request or message me

## API Documentation

You can find a tutorial on how to write your own plugins in this repository's [wiki](https://github.com/nkreer/Fish/wiki).
If you have any questions regarding the API, feel free to send me an E-Mail.

## Contribute

All contributions and Pull Requests are welcome. Just stick to the original coding style wherever possible.

## Why?

Fish was created because I found it really hard to work with existing frameworks like Phergie. 
It was built to be much easier to use and to do much more on its own. I just don't like the idea of having to install plugins
for the simplest of things like command-handling, management and replying to PINGs. 
Fish abstracts the protocol away completely and is, therefore, much easier to use with IDEs and code-completion software. 

## Test Fish

Unfortunately, I don't have the resources to provide a testing instance of the bot or a help-channel on a public IRC-network at the moment. Sorry.