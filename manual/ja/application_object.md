---
layout: default_ja
title: BEAR.Sunday | Application Object 
category: Application
---
# Application Object

The application object is an object that holds all of the service objects used by the application script to regulate the application runtime.

# Application Class 

The resource client, response and logger needed by the application script is passed to the constructor and is stored into their respective properties.

The relevant objects are injected for each interface according to the application configuration. For example when using a development configuration the development resource client provides more debugging information, in an API based application rather than have HTML output a component that outputs JSON+HAL(or just JSON) is used.

This class provides an instance through the instance script.