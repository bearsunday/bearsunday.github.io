---
layout: docs-en
title: Version
category: Manual
permalink: /manuals/1.0/en/version.html
---

# Version

## Supported PHP

BEAR.Sunday supports PHP version of the supported PHP ([Supported Versions](http://php.net/supported-versions.php)).

* `> = 5.6.0` (old stabilizer 28 Aug 2014 - 31 Dec 2018)
* `> = 7.0.0` (old stabilizer 3 Dec 2015 - 3 Dec 2018)
* `> = 7.1.0` (Current stabilizer 1 Dec 2016 - 1 Dec 2019)

End of life ([EOL](http://php.net/eol.php))

* `5.5. *` (21 Jul 2016)

The new optional package will be developed based on the current stable PHP. We encourage you to use the current stable PHP for security and performance and security.

You can check the install version detail at [BEAR.SupportedVersions](https://travis-ci.org/bearsunday/BEAR.SupportedVersions).

## Semver

BEAR.Sunday follows [Semantic Versioning](http://
semper.org/lang/en/). It is not necessary to modify the application code in minor version upgrade .

`composer update` can be done at any time for packages.

## Version Policy

When PHP comes to EOL and upgraded in major version (`5.6` →` 7.0`), BEAR.Sunday still does not breack BC for application code. Even though the version number of PHP necessary to use the new module may rise, there is no need for destructive change for that.


BEAR.Sunday emphasizes that the code is clean and available for a long time.

## Package version

The version of the framework does not lock the version of the library. The library can be updated regardless of the version of the framework.
