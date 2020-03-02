# yii2-jsloader

[![Latest Stable Version](https://poser.pugx.org/ischenko/yii2-jsloader/v/stable)](https://packagist.org/packages/ischenko/yii2-jsloader)
[![Total Downloads](https://poser.pugx.org/ischenko/yii2-jsloader/downloads)](https://packagist.org/packages/ischenko/yii2-jsloader)
[![Build Status](https://travis-ci.org/ischenko/yii2-jsloader.svg?branch=master)](https://travis-ci.org/ischenko/yii2-jsloader)
[![Code Climate](https://codeclimate.com/github/ischenko/yii2-jsloader/badges/gpa.svg)](https://codeclimate.com/github/ischenko/yii2-jsloader)
[![Test Coverage](https://codeclimate.com/github/ischenko/yii2-jsloader/badges/coverage.svg)](https://codeclimate.com/github/ischenko/yii2-jsloader/coverage)
[![License](https://poser.pugx.org/ischenko/yii2-jsloader/license)](https://packagist.org/packages/ischenko/yii2-jsloader)

An extension provides behavior which allows to process js files, code and asset bundles by various js loaders such as [RequireJS](http://requirejs.org).

Along with behavior it provides a set of interfaces and base classes for implementing a js loader.

Currently available implementations of js loaders are:
 - [yii2-jsloader-requirejs](https://github.com/ischenko/yii2-jsloader-requirejs)
 - [yii2-jsloader-systemjs](https://github.com/ischenko/yii2-jsloader-systemjs)

## Installation
*Requires PHP >= 7.1

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run
```
composer require ischenko/yii2-jsloader
```

or add

```json
"ischenko/yii2-jsloader": "*"
```

to the `require` section of your composer.json.

## Usage

Add the behavior and concrete loader implementation to a view configuration

```php
    ...
    'components' => [
        ...
        'view' => [
            'as jsLoader' => [
                'class' => 'ischenko\yii2\jsloader\Behavior',
                'loader' => [
                    'class' => 'loader\namespace\LoaderClass'
                ]
            ]
        ]
        ...
    ]
    ...
```

By default the loader skips scripts and bundles/files located in [the head section](http://www.yiiframework.com/doc-2.0/yii-web-view.html#POS_HEAD-detail), 
but if you need to include those scripts or exclude another section(s) you can do this via `ignorePositions` property:

```php
    ...
    'components' => [
        ...
        'view' => [
            'as jsLoader' => [
                'class' => 'ischenko\yii2\jsloader\Behavior',
                'loader' => [
                    'class' => 'loader\namespace\LoaderClass',
                    'ignorePositions' => [
                        View::POS_HEAD,
                        View::POS_BEGIN
                    ]
                ]
            ]
        ]
        ...
    ]
    ...
```

Additionally you can set a list of an asset bundles that should be ignored by the loader via `ignoreBundles` property:

```php
    ...
    'components' => [
        ...
        'view' => [
            'as jsLoader' => [
                'class' => 'ischenko\yii2\jsloader\Behavior',
                'loader' => [
                    'class' => 'loader\namespace\LoaderClass',
                    'ignoreBundles' => [
                        'app\assets\AppCssAsset'
                    ]
                ]
            ]
        ]
        ...
    ]
    ...
```
