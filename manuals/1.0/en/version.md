---
layout: docs-en
title: Version
category: Manual
permalink: /manuals/1.0/en/version.html
---

# Version

## Supported PHP

BEAR.Sunday supports the following supported PHP versions ([Supported Versions](http://php.net/supported-versions.php)):

* `^5.6.0` (old stable 28 Aug 2014 - 31 Dec 2018)
* `^7.0.0` (old stable 3 Dec 2015 - 3 Dec 2018)
* `^7.1.0` (Current stable 1 Dec 2016 - 1 Dec 2019)

End of life ([EOL](http://php.net/eol.php))

* `5.5.*` (21 Jul 2016)

The new optional package will be developed based on the current stable PHP. We encourage you to use the current stable PHP for quality, performance and security.

You can check the install version detail at [BEAR.SupportedVersions](https://travis-ci.org/bearsunday/BEAR.SupportedVersions).

## Semver

BEAR.Sunday follows [Semantic Versioning](http://
semper.org/lang/en/). It is not necessary to modify the application code on minor version upgrades.

`composer update` can be done at any time for packages.

## Version Policy

When PHP comes to EOL and upgraded in major version (ex. `5.6` →` 7.0`), BEAR.Sunday still does not break the BC of the application code. Even though the version number of PHP that is necessary to use the new module becomes higher, changes to the application codes are not needed.


BEAR.Sunday emphasizes clean code and longevity.

## Package version

The version of the framework does not lock the version of the library. The library can be updated regardless of the version of the framework.
