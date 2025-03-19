---
layout: docs-en
title: Introduction
category: Manual
permalink: /manuals/1.0/en/
---

# What is BEAR.Sunday?

BEAR.Sunday is a PHP application framework that combines clean object-oriented design with a resource-oriented architecture aligned with the fundamental principles of the web. This framework emphasizes compliance with standards, a long-term perspective, high efficiency, flexibility, self-description, and importantly, simplicity.

## Framework

BEAR.Sunday consists of three frameworks.

`Ray.Di` interfaces object dependencies based on the [Principle of Dependency Inversion](http://en.wikipedia.org/wiki/Dependency_inversion_principle).

`Ray.Aop` connects core concerns and cross-cutting concerns with [aspect-oriented programming](http://en.wikipedia.org/wiki/Aspect-oriented_programming).

`BEAR.Resource` connects application data and functionality with resources with [REST constraints](https://en.wikipedia.org/wiki/Representational_state_transfer).

The framework provides constraints and design principles that guide the entire application, promoting consistent design and implementation, and resulting in high-quality, clean code.

## Libraries

Unlike full-stack frameworks, BEAR.Sunday does not include its own libraries for specific tasks like authentication or database management. Instead, it favors the use of high-quality third-party libraries.

This approach is based on two key design philosophies: firstly, the belief that "frameworks remain, libraries change," acknowledging that while the framework provides a stable foundation, libraries evolve to meet changing needs over time. Secondly, it empowers "application architects with the right and responsibility to choose libraries" that best fit their application's requirements, constraints, and goals.

BEAR.Sunday draws a clear distinction between frameworks and libraries, emphasizing the role of the framework as an application constraint.

## Architecture

BEAR.Sunday departs from the traditional MVC (Model-View-Controller) architecture, embracing a resource-oriented architecture (ROA). In this paradigm, data and business logic are unified as resources, and the design revolves around links and operations on those resources. While ROA is commonly used for REST API design, BEAR.Sunday extends it to the entire web application.

## Long-term perspective

BEAR.Sunday is designed with a long-term view, focusing on application maintainability:

- **Constraints**: The consistent application constraints imposed by DI, AOP, and REST remain unchanged over time.

- **Eternal 1.x**:The System That Never Breaks Backward Compatibility. Since its initial release in 2015, BEAR.Sunday has continuously evolved without introducing any backward-incompatible changes. This steadfast approach eliminates the need for compatibility fixes and their associated testing, thereby preventing future technical debt. The system remains cutting-edge, ensuring easy upgrades and access to the latest features without compatibility concerns.

- **Standards Compliance**: BEAR.Sunday adheres to various standards, including HTTP, JsonSchema, and others. For DI, it follows Google Guice, and for AOP, it aligns with the Java Aop Alliance.

## Connectivity

BEAR.Sunday transcends traditional web applications, offering seamless integration with a diverse range of clients:

- **HTTP Client**: All resources are directly accessible via HTTP, unlike models or controllers in MVC.

- **Console Access**:
  Resources can be accessed directly from the console without changing the source code, allowing the same resources to be used from both web and command-line interfaces. Additionally, BEAR.CLI enables resources to be distributed as standalone UNIX commands through Homebrew.

- **composer package**: Resources from applications installed under the vendor directory via Composer can be invoked directly, enabling coordination between multiple applications without resorting to microservices.

- **Multilingual framework**: BEAR.Thrift facilitates seamless and efficient interoperability with other languages and PHP versions.

## Web Cache

By integrating resource-oriented architecture with modern CDN technology, we achieve distributed caching that surpasses traditional server-side TTL caching. BEAR.Sunday's design philosophy adheres to the fundamental principles of the Web, utilizing a CDN-centered distributed caching system to ensure high performance and availability.

- **Distributed Caching**: By caching on the client, CDN, and server-side, both CPU and network costs are minimized.

- **Identification**: ETag-based verification ensures that only modified content is retrieved, enhancing network efficiency.

- **Fault tolerance**: Event-based cache invalidation allows all content to be stored in CDN caches without TTL limitations. This improves fault tolerance to the point where the system remains available even if the PHP or database servers go down.


## Performance

BEAR.Sunday is designed with a focus on performance and efficiency while maintaining maximum flexibility. This approach enables a highly optimized bootstrap, positively impacting both user experience and system resources. Performance is always one of the primary concerns for BEAR.Sunday, playing a central role in our design and development decisions.

## Because Everything is a Resource

BEAR.Sunday embraces the essence of the Web, where "Everything is a Resource." As a PHP web application framework, it excels by providing superior constraints based on object-oriented and REST principles, applicable to the entire application.

These constraints encourage developers to design and implement consistently and improve the quality of the application in the long run. At the same time, the constraints provide developers with freedom and enhance creativity in building the application.
