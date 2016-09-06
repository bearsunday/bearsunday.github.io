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

Ray.Aop is an `Aspect Orietated Programming` framework that allows you to wrap objects with other objects by binding them together. This is great when your core business logic should have no knowledge that it is being wrapped by for example an authentication or logging aspect.


### BEAR.Resource

BEAR.Resource allows you to treat all entry points to your app as RESTful resources. Resources can then be consumed uniformly across your app. This can be done externally via HTTP or internally via the resource client.

Treating all of your data as a resource then simplifies the how you get to your app.

## Libraries

BEAR.Sunday is not like other MVC frameworks and contains libraries that support databases, authentication or the like.

Instead just hook in any of the great libraries that are available on [Packagist](https://packagist.org/) like the [Aura](http://auraphp.com/) component libraries.

## Resource Orientated Pattern

BEAR.Sunday does not follow MVC instead it is a variation of [Resource-Method-Representation](http://www.peej.co.uk/articles/rmr-architecture.html)

![4R](/images/screen/4r.png)

Resource state is created by calling stateless  requests on RESTful methods, the internal renderer then handles the `representation` and becomes the response.

## Components

### Resources

What initially makes up a web application is a group of resources. In BEAR.Sunday these can be created as Resource Objects. The resources can then be accessed locally via PHP or by HTTP requests. In both cases using a consistent URI interface. Each Resource object can then be exposed as a service either or both inside and outside of you application. Using Hypermedia using specially provided annotations you can either `@Link` or `@Embed` other resources.

### Methods

Each resource object is accessible through an HTTP verb based method. HTTP parameters can also be specified in the method and are then able to be passed in. Once invoked resource properties can then be constructed inside this method. Just like in a controller action in an MVC framework other domain models or resources can be called and accessed.

Method construction can then be built up using a variation of [Onion Architecture](http://www.infoq.com/news/2014/10/ddd-onion-architecture) or [Clean Architecture](http://blog.8thlight.com/uncle-bob/2012/08/13/the-clean-architecture.html). Logging, validation or authentication etc can then be implemented using `Aspect Orientated Programming` wrapping the original target method.

### Representation

Each resource have have the representation of its current state rendered through an injected renderer. Methods themselves do not know anything about a representation's existence at all. This representation is handled by the resources' `Responder`

![Clean Method](/images/screen/clean-method.png)

## Collaboration

1. A web handler transforms a client request into an application resource request.

1. The `Method` that recieved the `Resource` request then constructs itself (the resource).

1. The resources assigned `Renderer` then renders the string value `Representation` of that resources state.

1. The `Responder` contained in the `Resource` then returns the `Representation` to the client

## Why a new pattern?

The conventional object orientated pattern maps an application to HTTP. Controllers know nothing about HTTP or REST.

In this new pattern you create an object that is mapped to HTTP. Making REST the framework in which do your development in. Unlocking the power of REST this `Resource Orientated` pattern allows you to use HTTP as the application protocol.
