---
layout: default
title: BEAR.Sunday | Introduction
category: Getting Started
subcategory: Installation
---

<img src="{{ site.url }}/images/screen/diagram.png" style="max-width: 100%;height: auto;"/>

## No components

BEAR.Sunday offers no libraries of its own.
PHP namespaces, PSR, a new coding github culture, unit testing, a new trend of library oriented frameworks... these have all happened recently and push us to a more library oriented way of thinking.
So BEAR.Sunday chooses not to have it’s own components,
it uses others from aura, symfony and zend etc.

## Three object frameworks for connect

There are no libraries, instead, BEAR.Sunday offers three object frameworks.

 * Dependency Injection framework - connects object to object as dependency
 * Aspect Oriented Framework - connects domain logic to application logic
 * Hypermedia framework for object as a service - connects resource to resource, api to api.

## Everything is a resource

In BEAR.Sunday `everything` is a REST resource which leads to far simpler design and extensibility.
Interactions with your database, services and even pages and sections of your app all sit comfortably in a 
resource which can be consumed or rendered at will.

## Abstraction frameworks

BEAR.Sunday is abstraction framework.
`DSL`, `Annotation`, `URI`, `Interface`, `Aspects` and `Hypermedia`.
These abstraction technology encourage intentional-oriented code.

## Clean architecture

 * Dependency inversion principle.
 * Distinction between compilation and runtime.
 * One root object in a bootstrap.
 * Don’t repeat the same procedure.
 * Break up the concerns. Application logic and business logic by AOP
 * Aspect layering by context. Model interacted with differently depending on CONTEXT
 * AOP Compiler for type safety.
 * Contextual runtime injection by aspect.
 * AOP Standard.
 * Code intention, then bind implementation.
