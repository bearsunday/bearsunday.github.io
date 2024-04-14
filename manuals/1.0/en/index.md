---
layout: docs-en
title: Introduction
category: Manual
permalink: /manuals/1.0/en/
---

# What is BEAR.Sunday

BEAR.Sunday is a PHP application framework that combines a clean object-oriented design with a resource-oriented architecture that follows the basic principles of the Web. It emphasizes standards compliance, long-term perspective, high efficiency, flexibility, self-description, and simplicity.

## Framework

BEAR.Sunday consists of three frameworks.

`Ray.Di` interfaces object dependencies based on the [Principle of Dependency Inversion](http://en.wikipedia.org/wiki/Dependency_inversion_principle).

`Ray.Aop` connects intrinsic and transversal interests with [aspect-oriented programming](http://en.wikipedia.org/wiki/Aspect-oriented_programming).

`BEAR.Resource` connects application data and functionality with resources with [REST constraints](https://en.wikipedia.org/wiki/Representational_state_transfer).

The framework is a set of constraints and design principles that apply to the entire application. It promotes consistent design and implementation and empowers you to build high-quality, clean applications.

## Libraries

Unlike full-stack frameworks, BEAR.Sunday does not provide proprietary libraries for specific tasks such as authentication or databases. Instead, it prefers to use high-quality third-party libraries.

This approach is based on two design philosophies: the first is that the framework remains the same, but the libraries change. While frameworks continue to provide a stable structure as the foundation of the application, libraries evolve over time to meet the specific needs of the application.

The second is that "the application architect has the right and responsibility to select the library. The application architect is entrusted with the ability and responsibility to select the library that best meets the requirements, constraints, and objectives of the application.

BEAR.Sunday makes a clear distinction between frameworks and libraries as "fads" and emphasizes the role of frameworks as application constraints.

## Architecture

BEAR.Sunday differs from the traditional Model-View-Controller (MVC) architecture by adopting a Resource Oriented Architecture (ROA). In this architecture, data and business logic are treated as unified resources, and the design of the application is centered on linking and manipulating them. Resource Oriented Architecture is widely used in the design of REST APIs, but BEAR.Sunday applies it to the design of entire web applications.

## Long-term perspective

BEAR.Sunday is designed with the long-term sustainability of your application in mind.

- **Constraints**: consistent application constraints that follow DI, AOP, and REST constraints will not change over time.

- **Eternal 1.x**: no backward compatibility breaking changes since the 1.0 release in 2015. There is no liability of needing periodic compatibility-enabling retrofits and their testing. Applications can always be upgraded to the latest version and there is no liability for compatibility retrofits and testing.

- **Standards Compliance**: Follows standards such as HTTP standard, JsonSchema, etc., DI is based on Google Guice, AOP is based on Java's Aop Alliance.

## Connectivity

BEAR.Sunday enables seamless integration with a wide variety of clients beyond web applications.

- **HTTP Client**:.
  All resources can be accessed using HTTP; unlike MVC models and controllers, BEAR.Sunday's resources can be accessed directly from the client.

- **composer package**:.
  composer allows direct invocation of the resources of applications installed under vednor. Multiple applications can be coordinated without the need to use microservices.

- **Multilingual framework**: BEAR.
  Thrift allows you to work with non-PHP languages and different versions of PHP.

## Web Cache

Resource-oriented architecture enables distributed caching, which is inherent to the Web. BEAR.Sunday's design philosophy follows the basic principles of the Web and leverages a distributed caching system centered on a CDN to achieve the high performance and BEAR.Sunday's design philosophy is in line with the basic principles of the Web.

- **Distributed Caching**: Storing caches on the client, CDN, and server side reduces both CPU and network costs.

- **IDENTIFICATION**:.
  Improves network efficiency by using ETag to check the identity of cached content and only re-retrieve content if it has changed.

- **Fault tolerance**:.
  By employing event-driven content, a system based on CDN caching with no expiration date on the cache will continue to serve content even if the PHP or DB is down.

## Performance

BEAR.Sunday is designed with a focus on performance and efficiency while ensuring maximum flexibility.

- **Injection and Compilation**: Dependency injection is done at compile time to minimize runtime overhead and get applications up and running faster.

- **Caching**:.
  Resource-oriented architecture enables CDN-centric caching, minimizing PHP execution and database access.

- **Fast Launch**:.
  Compiling framework functionality as a root object allows for optimized bootstrapping and ultra-fast response using runtimes such as Swoole.

## Because Everything is a Resource

Because Everything is a Resource, BEAR.Sunday is a PHP web application framework designed around the essence of the web: resources. Its real value lies in providing excellent constraints based on object-oriented and REST principles as constraints for the entire application.

These constraints encourage developers to design and implement consistently and improve the quality of the application in the long run. At the same time, the constraints provide developers with freedom and enhance creativity in building the application.
