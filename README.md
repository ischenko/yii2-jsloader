# yii2-jsloader

An extension provides behavior which allows to process js files, code and asset bundles by various js loaders such as [RequireJS](http://requirejs.org).

[![Build Status](https://travis-ci.org/ischenko/yii2-jsloader.svg?branch=master)](https://travis-ci.org/ischenko/yii2-jsloader)
[![Code Climate](https://codeclimate.com/github/ischenko/yii2-jsloader/badges/gpa.svg)](https://codeclimate.com/github/ischenko/yii2-jsloader)
[![Test Coverage](https://codeclimate.com/github/ischenko/yii2-jsloader/badges/coverage.svg)](https://codeclimate.com/github/ischenko/yii2-jsloader/coverage)

Along with behavior it provides a set of interfaces and base classes for implementing a js loader.

Currently available implementations of js loaders are:
 - [yii2-jsloader-requirejs](https://github.com/ischenko/yii2-jsloader-requirejs)

## Installation
*Requires PHP >= 5.4.*

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run
```
composer require ischenko/yii2-jsloader
```

or add

```json
"ischenko/yii2-jsloader": "^1.0",
```

to the `require` section of your composer.json.

**License:** MIT
