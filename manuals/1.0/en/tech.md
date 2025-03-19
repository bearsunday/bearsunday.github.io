---
layout: docs-en
title: Technology
category: Manual
permalink: /manuals/1.0/en/tech.html
---
# Technology

The distinctive technologies and features of BEAR.Sunday are explained in the following chapters. 

* [Architecture and Design Principles](#architecture-and-design-principles)
* [Performance and Scalability](#performance-and-scalability)
* [Developer Experience](#developer-experience)
* [Extensibility and Integration](#extensibility-and-integration)
* [Design Philosophy and Quality](#design-philosophy-and-quality)
* [The Value BEAR.Sunday Brings](#the-value-bearsunday-brings)

## Architecture and Design Principles

### Resource Oriented Architecture (ROA)

BEAR.Sunday's ROA is an architecture that realizes RESTful API within a web application. It is the core of BEAR.Sunday's design principles, functioning as both a hypermedia framework and a service-oriented architecture. Similar to the Web, all data and functions are considered resources and are operated through standardized interfaces such as GET, POST, PUT, and DELETE.

#### URI

URI (Uniform Resource Identifier) is a key element to the success of the Web and is also at the heart of BEAR.Sunday's ROA. By assigning URIs to all resources handled by the application, resources can be easily identified and accessed. URIs not only function as identifiers for resources but also express links between resources.

#### Uniform Interface

Access to resources is done using HTTP methods such as GET, POST, PUT, and DELETE. These methods specify the operations that can be performed on resources and provide a common interface regardless of the type of resource.

#### Hypermedia

In BEAR.Sunday's Resource Oriented Architecture (ROA), each resource provides affordances (available operations and functions for the client) through hyperlinks. These links represent the operations that clients can perform and guide navigation within the application.

#### Separation of State and Representation

In BEAR.Sunday's ROA, the state of a resource and its representation are clearly separated. The state of the resource is managed by the resource class, and the renderer injected into the resource converts the state of the resource into a resource state representation in various formats (JSON, HTML, etc.). Domain logic and presentation logic are loosely coupled, and even with the same code, changing the binding of the state representation based on the context will also change the representation.

#### Differences from MVC

BEAR.Sunday's ROA (Resource Oriented Architecture) takes a different approach from the traditional MVC architecture. MVC composes an application with three components: model, view, and controller. The controller receives a request object, controls a series of processes, and returns a response. In contrast, a resource in BEAR.Sunday, following the Single Responsibility Principle (SRP), only specifies the state of the resource in the request method and is not involved in the representation.

While there are no constraints on the relationship between controllers and models in MVC, resources have explicit constraints on including other resources using hyperlinks and URIs. This allows for declarative definition of content inclusion relationships and tree structures while maintaining information hiding of the called resources.

MVC controllers manually retrieve values from the request object, while resources declaratively define the required variables as arguments to the request method. Therefore, input validation is also performed declaratively using JsonSchema, and the arguments and their constraints are documented.

### Dependency Injection (DI)

Dependency Injection (DI) is an important technique for enhancing the design and structure of applications in object-oriented programming. The central purpose of DI is to divide an application's responsibilities into multiple components with independent domains or roles and manage the dependencies between them.

DI helps to horizontally divide one responsibility into multiple functions. The divided functions can be developed and tested independently as "dependencies". By injecting those dependencies with clear responsibilities and roles based on the single responsibility principle from the outside, the reusability and testability of objects are improved. Dependencies can also be vertically divided into other dependencies, forming a tree of dependencies.

BEAR.Sunday's DI uses a separate package called [Ray.Di](https://github.com/ray-di/Ray.Di), which adopts the design philosophy of Google's DI framework Guice and covers almost all of its features.

It also has the following characteristics:

* Bindings can be changed by context, allowing different implementations to be injected during testing.
* Attribute-based configuration enhances the self-descriptiveness of the code.
* Ray.Di performs dependency resolution at compile-time, improving runtime performance. This is different from other DI containers that resolve dependencies at runtime.
* Object dependencies can be visualized as a graph. Example: [Root Object](/images/app.svg).

<img src="https://ray-di.github.io/images/logo.svg" width="180" alt="Ray.Di logo">

### Aspect Oriented Programming (AOP)

Aspect-Oriented Programming (AOP) is a pattern that realizes flexible applications by separating essential concerns such as business logic from cross-cutting concerns such as logging and caching. Cross-cutting concerns refer to functions or processes that span across multiple modules or layers. It is possible to bind cross-cutting processes based on search conditions and flexibly configure them based on context.

BEAR.Sunday's AOP uses a separate package called Ray.Aop, which declaratively binds cross-cutting processes by attaching PHP attributes to classes and methods. Ray.Aop conforms to Java's [AOP Alliance](https://aopalliance.sourceforge.net/).

AOP is often misunderstood as a technology that "has the strong power to break the existing order". However, its raison d'être is not to exercise power beyond constraints but to complement areas where object-orientation is not well-suited, such as exploratory assignment of functions using matchers and separation of cross-cutting processes. AOP is a paradigm that can create cross-cutting constraints for applications, in other words, it functions as an application framework.

## Performance and Scalability

### ROA-based Event-Driven Content Strategy with Modern CDN Integration

BEAR.Sunday realizes an advanced event-driven caching strategy by integrating with instant purge-capable CDNs such as Fastly, with Resource Oriented Architecture (ROA) at its core. Instead of invalidating caches based on the conventional TTL (Time to Live), this strategy immediately invalidates the CDN and server-side caches, as well as ETags (entity tags), in response to resource state change events.

By taking this approach of creating non-volatile and persistent content on CDNs, it not only avoids SPOF (Single Point of Failure) and achieves high availability and fault tolerance but also maximizes user experience and cost efficiency. It realizes the same distributed caching as static content for dynamic content, which is the original principle of the Web. It re-realizes the scalable and network cost-reducing distributed caching principle that the Web has had since the 1990s with modern technology.

#### Cache Invalidation by Semantic Methods and Dependencies

In BEAR.Sunday's ROA, each resource operation is given a semantic role. For example, the GET method retrieves a resource, and the PUT method updates a resource. These methods collaborate in an event-driven manner and efficiently invalidate related caches. For instance, when a specific resource is updated, the cache of resources that require that resource is invalidated. This ensures data consistency and freshness, providing users with the latest information.

#### Identity Confirmation and Fast Response with ETag

By setting ETags before the system boots, content identity can be quickly confirmed, and if there are no changes, a 304 Not Modified response is returned to minimize network load.

#### Partial Updates with Donut Caching and ESI

BEAR.Sunday adopts a donut caching strategy and uses ESI (Edge Side Includes) to enable partial content updates at the CDN edge. This technology allows for dynamic updates of only the necessary parts without re-caching the entire page, improving caching efficiency.

In this way, BEAR.Sunday and Fastly's integration of ROA-based caching strategy not only realizes advanced distributed caching but also enhances application performance and fault tolerance.

### Accelerated Startup

In the original world of DI, users avoid dealing directly with the injector (DI container) as much as possible. Instead, they generate a single root object at the application's entry point to start the application. In BEAR.Sunday's DI, there is virtually no DI container manipulation even at configuration time. The root object is huge but is a single variable, so it is reused beyond requests, realizing an optimized bootstrap to the limit.

## Developer Experience

### Ease of Testing

BEAR.Sunday allows for easy and effective testing due to the following design features:

* Each resource is independent, and testing is easy due to the stateless nature of REST requests.
  Since the state and representation of resources are clearly separated, it is possible to test the state of resources even when they are in HTML representation.
* API testing can be performed while following hypermedia links, and tests can be written in the same code for PHP and HTTP.
* Different implementations are bound during testing through context-based binding.

### API Documentation Generation

API documentation is automatically generated from the code. It maintains consistency between code and documentation and improves maintainability.

### Visualization and Debugging

Utilizing the technical feature of resources rendering themselves, during development, the scope of resources can be indicated on HTML, resource states can be monitored, and PHP code and HTML templates can be edited in an online editor and reflected in real-time.

## Extensibility and Integration

### Integration of PHP Interfaces and SQL Execution

In BEAR.Sunday, the execution of SQL statements for interacting with databases can be easily managed through PHP interfaces. It is possible to directly bind SQL execution objects to PHP interfaces without implementing classes. The boundary between the domain and infrastructure is connected by PHP interfaces.

In that case, types can also be specified for arguments, and any missing parts are dependency-resolved by DI and used as strings. Even when the current time is needed for SQL execution, there is no need to pass it; it is automatically bound. This helps keep the code concise as the client is not responsible for passing all arguments.

Moreover, direct management of SQL makes debugging easier when errors occur. The behavior of SQL queries can be directly observed, allowing for quick identification and correction of problems.

### Integration with Other Systems

BEAR.Sunday resources can be accessed through various interfaces. In addition to web interfaces, resources can be accessed directly from the console, allowing the same resources to be used from both web and command-line interfaces without changing the source code. Furthermore, using BEAR.CLI, resources can be distributed as standalone UNIX commands. Multiple BEAR.Sunday applications can also run concurrently within the same PHP runtime, enabling collaboration between independent applications without building microservices.

### Stream Output

By assigning streams such as file pointers to the body of a resource, large-scale content that cannot be handled in memory can be output. In that case, streams can also be mixed with ordinary variables, allowing flexible output of large-scale responses.

### Gradual Migration from Other Systems

BEAR.Sunday provides a gradual migration path and enables seamless integration with other frameworks and systems such as Laravel and Symfony. This framework can be implemented as a Composer package, allowing developers to gradually introduce BEAR.Sunday's features into their existing codebase.

### Flexibility in Technology Migration

BEAR.Sunday protects investments in preparation for future technological changes and evolving requirements. Even if there is a need to migrate from this framework to another framework or language, the constructed resources will not go to waste. In a PHP environment, BEAR.Sunday applications can be integrated as Composer packages and continuously utilized, and BEAR.Thrift allows efficient access to BEAR.Sunday resources from other languages. When not using Thrift, access via HTTP is also possible. SQL code can also be easily reused.

Even if the library being used is strongly dependent on a specific PHP version, different versions of PHP can coexist using BEAR.Thrift.

## Design Philosophy and Quality

### Adoption of Standard Technologies and Elimination of Proprietary Standards

BEAR.Sunday has a design philosophy of adopting standard technologies as much as possible and eliminating framework-specific standards and rules. For example, it supports content negotiation for JSON format and www-form format HTTP requests by default and uses the [vnd.error+json](https://github.com/blongden/vnd.error) media type format for error responses. It actively incorporates standard technologies and specifications such as adopting [HAL](https://datatracker.ietf.org/doc/html/draft-kelly-json-hal) (Hypertext Application Language) for links between resources and using [JsonSchema](https://json-schema.org/) for validation.

On the other hand, it eliminates proprietary validation rules and framework-specific standards and rules as much as possible.

### Object-Oriented Principles

BEAR.Sunday emphasizes object-oriented principles to make applications maintainable in the long term.

#### Composition over Inheritance

Composition is recommended over inheritance classes. Generally, directly calling a parent class's method from a child class can potentially increase the coupling between classes. The only abstract class that requires inheritance at runtime by design is the resource class `BEAR\Resource\ResourceObject`, but the methods of ResourceObject exist solely for other classes to use. There is no case in BEAR.Sunday where a user calls a method of a framework's parent class that they have inherited at runtime.

#### Everything is Injected

Framework classes do not refer to "configuration files" or "debug constants" during execution to determine their behavior. Dependencies corresponding to the behavior are injected. This means that to change the application's behavior, there is no need to change the code; only the binding of the implementation of the dependency to the interface needs to be changed. Constants like APP_DEBUG or APP_MODE do not exist. There is no way to know in what mode the software is currently running after it has started, and there is no need to know.

### Permanent Assurance of Backward Compatibility

BEAR.Sunday is designed with an emphasis on maintaining backward compatibility in the evolution of software and has continued to evolve without breaking backward compatibility since its release. In modern software development, frequent breaking of backward compatibility and the associated burden of modification and testing have become a challenge, but BEAR.Sunday has avoided this problem.

BEAR.Sunday not only adopts semantic versioning but also does not perform major version upgrades that involve breaking changes. It prevents new feature additions or changes to existing features from affecting existing code. Code that has become old and unused is given the attribute "deprecated" but is never deleted and does not affect the behavior of existing code. Instead, new features are added, and evolution continues.

Here's the English translation of the revised text:

### Acyclic Dependencies Principle (ADP)

The Acyclic Dependencies Principle states that dependencies should be unidirectional and non-circular. The BEAR.Sunday framework adheres to this principle and is composed of a series of packages with a hierarchical structure where larger framework packages depend on smaller framework packages. Each level does not need to be aware of the existence of other levels that encompass it, and the dependencies are unidirectional and do not form cycles. For example, Ray.Aop is not even aware of the existence of Ray.Di, and Ray.Di is not aware of the existence of BEAR.Sunday.

<img src="/images/screen/package_adp.png" width="360px" alt="Framework structure following the Acyclic Dependencies Principle">

As backward compatibility is maintained, each package can be updated independently. Moreover, there is no version number that locks the entire system, as seen in other frameworks, and there is no mechanism for object proxies that hold cross-cutting dependencies between objects.

The Acyclic Dependencies Principle is in harmony with the DI (Dependency Injection) principle, and the root object generated during the bootstrapping process of BEAR.Sunday is also constructed following the structure of this Acyclic Dependencies Principle.

[<img src="/images/screen/clean-architecture.png" width="40%">](/images/screen/clean-architecture.png)

The same applies to the runtime. When accessing a resource, first, the cross-cutting processing of the AOP aspects bound to the method is executed, and then the method determines the state of the resource. At this point, the method is not aware of the existence of the aspects bound to it. The same goes for resources embedded in the resource's state. They do not have knowledge of the outer layers or elements. The separation of concerns is clearly defined.

### Code Quality

To provide applications with high code quality, the BEAR.Sunday framework also strives to maintain a high standard of code quality.

* The framework code is applied at the strictest level by both static analysis tools, Psalm and PHPStan.
* It maintains 100% test coverage and nearly 100% type coverage.
* It is fundamentally an immutable system and is so clean that initialization is not required every time, even in tests. It unleashes the power of PHP's asynchronous communication engines like Swoole.

## The Value BEAR.Sunday Brings

### Value for Developers

* Improved productivity: Based on robust design patterns and principles with constraints that don't change over time, developers can focus on core business logic.
* Collaboration in teams: By providing development teams with consistent guidelines and structure, it keeps the code of different engineers loosely coupled and unified, improving code readability and maintainability.
* Flexibility and extensibility: BEAR.Sunday's policy of not including libraries brings developers flexibility and freedom in component selection.
* Ease of testing: BEAR.Sunday's DI (Dependency Injection) and ROA (Resource Oriented Architecture) increase the ease of testing.

### Value for Users

* High performance: BEAR.Sunday's optimized fast startup and CDN-centric caching strategy brings users a fast and responsive experience.
* Reliability and availability: BEAR.Sunday's CDN-centric caching strategy minimizes single points of failure (SPOF), allowing users to enjoy stable services.
* Ease of use: BEAR.Sunday's excellent connectivity makes it easy to collaborate with other languages and systems.

### Value for Business

* Reduced development costs: The consistent guidelines and structure provided by BEAR.Sunday promote a sustainable and efficient development process, reducing development costs.
* Reduced maintenance costs: BEAR.Sunday's approach to maintaining backward compatibility increases technical continuity and minimizes the time and cost of change response.
* High extensibility: With technologies like DI (Dependency Injection) and AOP (Aspect Oriented Programming) that change behavior while minimizing code changes, BEAR.Sunday allows applications to be easily extended in line with business growth and changes.
* Excellent User Experience (UX): BEAR.Sunday provides high performance and high availability, increasing user satisfaction, enhancing customer loyalty, expanding the customer base, and contributing to business success.

Excellent constraints do not change. The constraints brought by BEAR.Sunday provide specific value to developers, users, and businesses respectively.

BEAR.Sunday is a framework designed based on the principles and spirit of the Web, providing developers with clear constraints to empower them to build flexible and robust applications.
