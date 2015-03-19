---
layout: docs-en
title: Introduction
category: Manual
permalink: /manuals/1.0/en/
---
# What is BEAR.Sunday?

BEAR.Sunday is a **framework** for creating elegant, truly RESTful web applications in PHP.

It helps you to create beautiful decoupled object orientated code. How does it do this?

## 3 Frameworks in 1

Many web frameworks give you lots of components, modules and features. BEAR.Sunday however gives you just 3 consistent **framework** patterns into which you can then wire up to your favourite libraries and custom gizmos.

### Ray.Di

Ray.Di is a modern and powerful `Dependency Injection` framework that enables this unobtrusive wiring up of dependencies, libraries and instantiated PHP objects.

### Ray.Aop

Ray.Aop is an `Aspect Orientated Programming` framework that allows you to wrap objects with other objects by binding them together. This is great when your core business logic should have no knowledge that it is being wrapped by for example an authentication or logging aspect.


### BEAR.Resource

BEAR.Resource allows you to treat all entry points to your app as RESTful resources. Resources can then be consumed uniformly across your app. This can be done externally via HTTP or internally via the resource client. 

No matter if you are already in your app or outside of it, treating all of your data as a resource then simplifies the how you get to it.

## Libraries

BEAR.Sunday is not like other MVC frameworks and contains libraries that support databases, authentication or the like. 

Instead just hook in any of the great libraries that are available on Packagist like the Aura component libraries.

