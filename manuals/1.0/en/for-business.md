---
layout: docs-en
title: For Decision Makers
category: Manual
permalink: /manuals/1.0/en/for-business.html
---

# Why BEAR.Sunday?

A guide for technical leaders and decision makers evaluating PHP frameworks.

## Executive Summary

BEAR.Sunday is a PHP framework designed for **long-term business value**:

- **Zero forced migrations** - Backward compatibility guaranteed since 2015
- **Lower total cost of ownership** - Less maintenance, fewer rewrites
- **Performance at scale** - CDN-native architecture reduces infrastructure costs

## Long-Term Perspective on Framework Selection

When selecting a framework, consider long-term costs alongside initial productivity:

| Cost Factor | Typical Framework | BEAR.Sunday |
|-------------|-------------------|-------------|
| Major version migrations | Every 2-3 years | Never required |
| Breaking changes | Each major release | None since release |
| Rewrite cycles | Common | Unnecessary |
| Technical debt accumulation | Gradual | Constrained by design |

**Real-world impact**: Mainstream frameworks may require significant refactoring every 2-3 years to keep up with LTS versions. It's not uncommon to see projects stuck on outdated versions, running with security risks. **BEAR.Sunday applications written for PHP5 run unchanged on PHP8.5.**

## Total Cost of Ownership

### Year 1: Similar Investment

Initial development costs are comparable to other frameworks. BEAR.Sunday's learning curve is offset by:
- Cleaner architecture from the start
- Fewer "magic" behaviors to debug
- Explicit dependencies (easier onboarding)

### Years 2-5: The Difference Emerges

| Activity | Typical Framework | BEAR.Sunday |
|----------|-------------------|-------------|
| Security patches | Apply & test for breaks | Apply directly |
| PHP upgrades | May require framework upgrade | Seamless |
| Feature additions | Fight existing architecture | Extend cleanly |
| New team members | Learn framework quirks | Learn principles |

### Years 5-10: Compound Savings

- No major migration projects
- Original codebase remains maintainable
- Performance improvements via module updates (e.g., parallel execution)
- Code written years ago gains new capabilities without modification

## Addressing Common Concerns

### "It's not as popular as Laravel/Symfony"

**Popularity is not a business requirement.** Consider:

- **Hiring**: BEAR.Sunday uses standard PHP, PSR standards, and well-known patterns (DI, REST). Any skilled PHP developer can contribute.
- **Community**: Smaller but focused. Direct access to maintainers.
- **Longevity**: 10+ years of consistent development without breaking changes.

### "Will it be maintained?"

BEAR.Sunday's architecture ensures longevity:

- Built on standalone packages (Ray.Di, Ray.Aop) used independently
- No "big bang" rewrites planned or needed
- Backward compatibility is a core design principle, not an afterthought

### "Can we find developers?"

BEAR.Sunday developers are PHP developers who understand:
- Dependency Injection (same as Symfony)
- RESTful design (universal knowledge)
- Standard PSR interfaces

The framework enforces good practices, making any competent PHP developer productive quickly.

### "Won't onboarding take too long?"

Everything in BEAR.Sunday is **explicit and declarative**:

- Dependencies declared in constructors
- Validation declared with JSON Schema
- SQL written in separate files
- Resource URIs are the routing

No "magic" like event dispatchers or static proxies means code behavior is clear from reading it. New team members don't need to research "what's happening behind the scenes in this framework."

### "Won't adapting to the framework be difficult?"

No need to "over-adapt" to BEAR.Sunday.

The essence of your application is **domain logic and SQL**. BEAR.Sunday resources are just a thin UI layer over them. A collection of resources becomes your application.

No need to contort your code to fit framework conventions. Your domain stays pure. No over-adaptation to an "ecosystem" means true continuity.

### "There are no books in bookstores"

True, there are no BEAR.Sunday books in bookstores. But none are needed.

What you learn are **universal principles, not framework-specific conventions**. DI, REST, clean architecture—books on these abound, and that knowledge directly applies to BEAR.Sunday. If a framework needs its own book, that's "learning cost," not "investment."

## Performance and Infrastructure Costs

### Architecture Optimized to the Limit

BEAR.Sunday's architecture is optimized to the point where "it's hard to make it faster":

- **Compile-time DI resolution** - Other DI containers resolve at runtime. BEAR.Sunday resolves in advance
- **Root object reuse** - The entire application is a single variable. Reused across requests
- **Immutable design** - No state means safe operation on async servers like Swoole
- **Zero framework overhead** - Bootstrap runs only once. Near-zero cost from second request onward

Benchmarks show performance close to raw PHP. There's virtually no "cost" to using the framework.

### CDN-Native Architecture

BEAR.Sunday's resource-oriented design enables aggressive caching:

- Event-driven cache invalidation (not TTL-based)
- Reduced origin server load
- Lower infrastructure costs at scale

**Continuous operation during outages**: Even when origin servers go down, CDN continues serving content from cache. Users don't notice service interruption. This level of fault tolerance is difficult to achieve with traditional architectures.

### Parallel Execution

Recent additions enable transparent parallel processing:

- No code changes required
- Existing applications benefit immediately
- Reduced response times = better user experience

### Preventive SQL Quality Management

The traditional approach is "fix it when it gets slow." Finding slow queries in AWS logs, fixing problems after they hit production—this is **treatment**.

In the BEAR.Sunday ecosystem, poor quality SQL is detected with EXPLAIN before deployment—this is **prevention**:

- Auto-detect full table scans, inefficient JOINs
- AI-powered optimization recommendations
- Create indexes and measure their impact in real-time
- Ineffective indexes automatically rolled back

**Visible cost reduction**: Before/after cost comparison reports are auto-generated, demonstrating improvements quantitatively. Continuous performance improvement is possible without hiring specialists.

**Why this is possible**: In BEAR.Sunday, SQL is a "first-class citizen" managed as independent files. Because SQL isn't embedded in code, AI agents can automatically handle the entire workflow: preparing test data, execution, creating improvement proposals, and verifying improvement effects. The architecture enables automation for the AI era.

## System Integration Without Microservices

When integrating multiple applications, microservices architecture is typically considered. However, microservices have challenges:

| Challenge | Microservices | BEAR.Sunday Import |
|-----------|--------------|-------------------|
| Network overhead | HTTP requests required | Direct calls within same process |
| Infrastructure complexity | Deploy per service | Integrate as Composer packages |
| Data consistency | Distributed transaction issues | Solvable in same process |
| Debugging | Trace across multiple services | Debug as single application |

**BEAR.Sunday unique feature**: Multiple BEAR.Sunday applications can work together as a single system without HTTP APIs.

```php
// Use resources from other apps directly
$weekday = $this->resource->get('app://other-app/weekday?year=2022&month=1&day=1');
```

Maintain microservices benefits (clear boundaries, independent deployment) while gaining monolith advantages (simplicity, performance).

## Multi-Language and Multi-Generation System Coordination

In enterprise systems, applications in different languages or PHP versions commonly coexist. BEAR.Sunday addresses this reality.

### Resource Sharing Across Languages

[BEAR.Thrift](https://github.com/bearsunday/BEAR.Thrift) enables access to BEAR.Sunday resources from multiple languages:

- **PHP** - Between applications on different PHP versions
- **Go** - From services requiring high performance
- **Python** - From data analysis/machine learning systems
- **Ruby** - From existing Rails applications

Communication uses the Thrift protocol, faster than HTTP, minimizing microservices communication overhead.

### Coexistence with Legacy Systems

Particularly important: it works with BEAR.Sunday applications running on **older PHP versions**:

- Existing system running on PHP 7.4
- New development on PHP 8.3
- Both connect seamlessly via Thrift

Gradual modernization becomes possible. No need to "rewrite everything."

### Resources Become Assets

**Resources become assets that transcend applications.** Once created, resources remain usable even after language or framework changes.

## Development Starting from Information Design

Many projects begin with "What API endpoints do we need?" BEAR.Sunday enables starting from the more fundamental "Information Architecture" instead.

### Why Information Design Matters

| Approach | Problem |
|----------|---------|
| Endpoint-centric | Discussions stay technical, requirements drift from business needs |
| Information design-centric | Common language for business and tech, early detection of requirement mismatches |

### Information Design with ALPS

[ALPS](https://alps.io/) (Application-Level Profile Semantics) defines application essence from three perspectives:

- **Vocabulary** - Defining business terms like "User," "Order"
- **Structure** - Classification and relationships of information
- **Operations** - What can be done, how states transition

This enables business and technical teams to share the same document from the requirements phase.

### Resilience to Technology Changes

Business domains defined in ALPS are independent of implementation technology:

- REST → GraphQL migration
- Monolith → Microservices transition
- Framework renewal

Even when technology changes, domain models remain usable. They accumulate as organizational knowledge assets.

## Frontend Team Collaboration

Backend-frontend coordination is a bottleneck in many projects. BEAR.Sunday streamlines collaboration through auto-generated documentation from your application.

### Auto-Generated Documentation

| Format | Purpose |
|--------|---------|
| **OpenAPI 3.1** | Frontend auto-generates API clients |
| **JSON Schema** | Share request/response types with frontend |
| **HTML ApiDoc** | Developer reference |
| **llms.txt** | AI tools understand your API |

### Code and Documentation in Sync

Because documentation is auto-generated from code:
- No "outdated documentation" problems
- API changes immediately reflected in docs
- Frontend always gets latest type definitions

### Example Workflow

1. Backend implements resources
2. GitHub Actions auto-generates and publishes OpenAPI/JSON Schema
3. Frontend auto-generates TypeScript types from OpenAPI
4. Type-safe API integration achieved

Eliminates back-and-forth like "Can I see the API spec?" or "Please send me the type definitions."

## AI-Assisted Development Productivity

BEAR.Sunday's explicit architecture works exceptionally well with AI-assisted development.

### Automated Test Generation

Clear boundaries and stateless design make it easy for AI to generate tests:

| Layer | Test Target | Characteristics |
|-------|-------------|-----------------|
| **SQL** | Individual SQL files | Clear input parameters and expected results |
| **Query Interface** | Data access layer | Interfaces defined with types |
| **Resource** | API endpoints | REST uniform interface |

Because each layer is stateless and independent:
- Testable without mocks
- AI easily understands boundaries
- Test case auto-generation is straightforward

The "no time to write tests" problem can be solved through AI auto-generation.

### Code Generation from ALPS

From an [ALPS](https://alps.io/) (Application-Level Profile Semantics) profile, you can auto-generate a complete code set:

- **Entity classes** - Domain objects
- **Query interfaces** - Data access layer
- **SQL files** - Database queries
- **Resource classes** - API endpoints
- **JsonSchema** - Validation definitions
- **Tests** - Smoke tests

**Development flow:**
1. Define API semantics in ALPS
2. Auto-generate complete code set
3. Implement business logic

Since implementation is generated from design documents (ALPS), documentation and code never drift apart.

### Natural Language Development

Combined with tools like Claude Code, natural language development becomes possible:

```text
"Generate a User resource with CRUD operations"
"Review this resource for BEAR.Sunday best practices"
```

Explicit dependencies and declarative architecture enable AI to accurately understand and generate code.

## Why PHP?

Before discussing frameworks, some stakeholders question the language itself. Here's why PHP remains a strong business choice:

### Market Reality

- **78% of websites** with server-side languages use PHP (W3Techs, 2024)
- Powers WordPress, Wikipedia, Facebook's backend infrastructure
- Largest talent pool among server-side languages

### Modern PHP

PHP has evolved significantly:

| Concern | Reality (PHP 8.x) |
|---------|-------------------|
| "PHP is slow" | JIT compilation, comparable to other interpreted languages |
| "PHP is insecure" | Modern frameworks prevent common vulnerabilities |
| "PHP is messy" | Type system, attributes, readonly properties |
| "PHP can't scale" | Facebook, Wikipedia, Slack prove otherwise |

### Business Advantages

- **Lower hiring costs** than Go, Rust, or Scala specialists
- **Extensive ecosystem** of libraries and tools
- **Simple deployment** compared to JVM or containerized solutions
- **Proven longevity** - 30 years of continuous development

### The Right Question

Instead of "Why PHP?", ask "What problem are we solving?"

For web applications with database backends, PHP offers:
- Rapid development
- Easy maintenance
- Abundant talent
- Proven reliability

BEAR.Sunday maximizes these advantages while adding architectural rigor typically associated with compiled languages.

## When BEAR.Sunday is the Right Choice

**Choose BEAR.Sunday when:**

- Application lifespan is 5+ years
- Maintenance cost predictability matters
- Team values clean architecture over rapid prototyping
- Performance at scale is a requirement
- You want to avoid framework lock-in

**Consider alternatives when:**

- Rapid prototyping is the priority
- Short-lived projects (< 2 years)
- Team strongly prefers a specific framework's conventions

## Making the Case

When presenting to stakeholders, focus on:

1. **Risk**: "No forced migrations means no surprise rewrite projects"
2. **Cost**: "Lower maintenance overhead over the application lifetime"
3. **Quality**: "Architectural constraints prevent common problems"
4. **Flexibility**: "Standard patterns mean we're not locked in"

## Track Record

BEAR.Sunday is proven in production at scale.

### Past Achievements

During Excite's heyday as a major portal site, BEAR.Sunday powered well-known services like **Excite Translation** and **Excite Blog**.

### Current Deployments

- **Bengo4.com** - Japan's largest legal consultation portal
- **Shueisha Media Sites** - High-traffic women's magazine sites including MAQUIA and SPUR

### Long-Term Operation Reality

Dozens of engineers have used BEAR.Sunday over the years, and **no project has ever become unsustainable due to code quality**. This validates BEAR.Sunday's design philosophy that "good constraints enforce good code."

## Cost Reduction Summary

| Cost Item | Traditional Approach | BEAR.Sunday |
|-----------|---------------------|-------------|
| **Migration** | Major refactoring every 2-3 years | Not required |
| **Technical debt repayment** | Increasing maintenance costs yearly | Prevented by constraints |
| **Onboarding** | Framework-specific learning | Apply universal principles |
| **Test creation** | Manual test design & implementation | AI auto-generation |
| **SQL optimization** | Expert investigation & tuning | Auto-detection & suggestions |
| **API/Frontend coordination** | Manual spec writing & syncing | Auto-generated, always current |
| **Incident response** | Complex failover configurations | Automatic CDN continuity |
| **Infrastructure** | Server scaling for growth | Minimized via CDN + optimization |
| **Documentation maintenance** | Manual updates, staleness risk | Auto-generated from code |
| **Team coordination** | Back-and-forth on specs | Eliminated via auto-generated docs |

## Even If You Leave BEAR.Sunday

The greatest fear in framework selection is "lock-in." BEAR.Sunday addresses this concern directly.

### All Artifacts Are Reusable

What you create with BEAR.Sunday is not framework-specific:

| Artifact | Reusability |
|----------|-------------|
| **SQL files** | Standard SQL. Usable with any framework |
| **Query interfaces** | Pure PHP interfaces. Implementations swappable |
| **Resource classes** | Pure PHP classes. Callable from other frameworks |
| **ALPS/JSON Schema** | Standard specifications. Language/framework agnostic |
| **Ray.Di/Ray.Aop** | Independent packages. Usable with Laravel/Symfony |
| **Aura PHP** | Router/PDO libraries. Same maintainer since PHP5 era |
| **Domain logic** | Designed framework-independent |

### HTML Sites Work as APIs

In BEAR.Sunday, applications built as HTML sites **function as APIs as-is**. Because resource state and representation (HTML/JSON) are separated, the same resource becomes HTML or JSON depending on context.

Other frameworks require building HTML controllers and API endpoints separately. With BEAR.Sunday, build once and support both.

### Calling Resources from Outside

BEAR.Sunday resources can be called directly from other frameworks like Laravel or Symfony:

```php
$resource = Injector::getInstance('MyVendor\MyApp', 'prod-app', $appDir)
    ->getInstance(ResourceInterface::class);
$user = $resource->get('/user', ['id' => 1]);
```

Build new systems while leveraging existing resources during the transition period.

### Learning Becomes Investment

What you learn with BEAR.Sunday isn't framework-specific conventions, but **universal software principles**:

- Dependency Injection (DI) - Applicable across languages and frameworks
- REST architecture - Foundational web development knowledge
- ALPS/Information design - API design best practices
- Clean architecture - Design principles

Framework-specific knowledge loses value when you leave that framework. Knowledge gained with BEAR.Sunday becomes an asset usable anywhere.

### Protecting Your Investment

- Code you write is never wasted
- Knowledge you gain is universal (as above)
- Gradual migration is possible (via BEAR.Thrift)

## Next Steps

1. **Evaluate**: Build a small proof-of-concept
2. **Compare**: Measure against your current approach
3. **Pilot**: Start with a non-critical service
4. **Expand**: Grow adoption based on results

---

*"Good constraints never change."*
