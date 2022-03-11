---
layout: docs-en
title: DI
category: Manual
permalink: /manuals/1.0/en/di.html
---
# DI

Dependency injection is basically providing the objects that an object needs (its dependencies) instead of having it construct them itself.

With dependency injection, objects accept dependencies in their constructors. To construct an object, you first build its dependencies. But to build each dependency, you need its dependencies, and so on. So when you build an object, you really need to build an object graph.

Building object graphs by hand is labour intensive, error prone, and makes testing difficult. Instead, **Dependency Injector** ([Ray.Di](https://github.com/ray-di/Ray.Di)) can build the object graph for you. 

| What is object graph ?
| Object-oriented applications contain complex webs of interrelated objects. Objects are linked to each other by one object either owning or containing another object or holding a reference to another object. This web of objects is called an object graph and it is the more abstract structure that can be used in discussing an application's state. - [Wikipedia](http://en.wikipedia.org/wiki/Object_graph)

Ray.Di is the core DI framework used in BEAR.Sunday, which is heavily inspired by Google [Guice](http://code.google.com/p/google-guice/wiki/Motivation?tm=6) DI framework.See more detail at [Ray.Di Manual](https://ray-di.github.io/manuals/1.0/en/index.html).