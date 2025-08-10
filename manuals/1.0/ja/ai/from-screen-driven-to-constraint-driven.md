---
layout: docs-ja
title: From Screen-Driven to Constraint-Driven Development
category: AI
permalink: /manuals/1.0/ja/ai/from-screen-driven-to-constraint-driven.html
---

# From Screen-Driven to Constraint-Driven Development

## The Paradigm Shift: Rethinking the Single Source of Truth

For decades, web development has been fundamentally **screen-driven**. Product managers provide mockups, developers implement UI components, and business logic gets molded around visual requirements. The **Single Source of Truth (SSOT)** has traditionally been the output image—the screen, the user interface, the final visual representation.

But what if we've been looking at this backwards?

## The Problem with Screen-Driven Development

### Traditional Flow: Output as SSOT

```
PM Mockup (SSOT) → UI Implementation → Backend Logic → Data Model → Tests
```

This approach creates a cascade of problems:

**1. Visual Bias Over Logic**
```javascript
// Typical screen-driven approach
class UserListComponent {
  // Implementation driven by UI requirements
  async loadUsers() {
    const users = await api.getUsers(); // What does this actually do?
    this.displayUsers(users); // How should this behave?
  }
}
```

The UI drives the API design, which drives the business logic, which drives the data model. Each layer is constrained by the visual requirements of the layer above it.

**2. Fragile Coupling**
When the UI changes (and it always does), the entire chain needs to be modified. A simple layout change can ripple through the entire system architecture.

**3. Knowledge Buried in Implementation**
The true business rules, constraints, and relationships are scattered across multiple layers of implementation code. The "what" and "why" get lost in the "how."

### The Hidden Cost: Cognitive Overload

```
Developer's Mental Model:
- What does this screen show?
- How should it behave when clicked?
- What data does it need?
- How do I fetch that data?
- Where do I validate inputs?
- What happens when it fails?
```

Every developer must reconstruct the business model from the visual presentation. This is not just inefficient—it's fundamentally backwards.

## Constraint-Driven Development: A New SSOT

### The Core Insight: Information Design as Foundation

What if instead of starting with screens, we start with **decomposed, digested, and understood information design**?

```
Information Design (SSOT) → Schema Design → Resource Design → Implementation → UI
```

### Step 1: Decompose the Problem Domain

Instead of looking at a "User Management Screen," we decompose into semantic elements:

```json
{
  "alps": {
    "descriptor": [
      {
        "id": "user",
        "type": "semantic",
        "def": "A person who uses the system",
        "descriptor": [
          {"id": "name", "type": "semantic", "def": "User's display name"},
          {"id": "email", "type": "semantic", "def": "User's email address"},
          {"id": "status", "type": "semantic", "def": "User's current status"}
        ]
      },
      {
        "id": "list_users",
        "type": "safe",
        "rt": "user",
        "def": "Retrieve a collection of users"
      },
      {
        "id": "create_user", 
        "type": "unsafe",
        "rt": "user",
        "def": "Create a new user in the system"
      }
    ]
  }
}
```

This ALPS (Application-Level Profile Semantics) description becomes our **true SSOT**. It captures:
- **What** entities exist (semantic types)
- **How** they can be manipulated (operations)
- **Why** each operation is safe or unsafe
- **When** operations are idempotent or not

### Step 2: Schema Design Emerges from Semantics

From our information design, schema constraints naturally emerge:

```sql
-- Generated from ALPS constraints
CREATE TABLE users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,  -- From semantic constraint
    email VARCHAR(255) UNIQUE NOT NULL,  -- From business constraint
    status ENUM('active', 'inactive') DEFAULT 'active',  -- From domain constraint
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

The database schema is no longer an implementation detail—it's a **direct manifestation of our semantic constraints**.

### Step 3: Resource Design Reflects True Capabilities

```php
class Users extends ResourceObject
{
    #[JsonSchema('user-create.json')]  // Schema constraint
    public function onPost(string $name, string $email): static  // Type constraint
    {
        // HTTP constraint: POST for creation returns 201
        $this->code = 201;
        // REST constraint: Location header for created resource
        $this->headers['Location'] = "/users/{$id}";
        // Business constraint: Return the created resource identifier
        $this->body = ['id' => $this->query->create($name, $email)];
        
        return $this;  // Resource constraint: return self
    }
    
    public function onGet(int $limit = 10, int $offset = 0): static
    {
        // HTTP constraint: GET is safe and cacheable
        $this->code = 200;
        // Business constraint: Paginated results
        $this->body = $this->query->list($limit, $offset);
        
        return $this;
    }
}
```

Each method is not implementing a UI requirement—it's **expressing a business capability** constrained by well-defined rules.

### Step 4: UI Becomes a View of Capabilities

```javascript
// UI now consumes well-defined capabilities
class UserManagementView {
  async loadUsers() {
    // Clear semantics: getting a collection of users
    const response = await this.resource.get('app://self/users');
    return response.body; // Well-defined structure
  }
  
  async createUser(userData) {
    // Clear semantics: creating a new user resource
    const response = await this.resource.post('app://self/users', userData);
    if (response.code === 201) {
      return response.headers.Location; // Well-defined success
    }
    throw new Error('Creation failed'); // Well-defined failure
  }
}
```

The UI becomes a **thin presentation layer** over well-defined, constraint-driven capabilities.

## The Constraint Hierarchy: Layered SSOT

In constraint-driven development, we don't have a single SSOT—we have a **hierarchy of truth**, where each layer constrains the next:

```
1. Semantic Constraints (ALPS)     ← The foundational truth
2. Data Constraints (SQL)          ← Constrained by semantics
3. Access Constraints (Interface)  ← Constrained by data model
4. Schema Constraints (JSON)       ← Constrained by access patterns
5. HTTP Constraints (Resource)     ← Constrained by schema
6. Cross-cutting Constraints (AOP) ← Constrained by HTTP behavior
7. Performance Constraints (Cache) ← Constrained by usage patterns
```

Each layer **inherits** constraints from above and **adds** its own. This creates a mathematically provable system where constraint satisfaction guarantees quality.

## The Hidden Problem: "It Works, Yay!" (IWY) Testing

Before we explore the full benefits of constraint-driven development, let's address a critical issue that most developers don't even realize they have.

What happens after we celebrate "It works, yay!"? **We write tests**.

But here's the trap: most tests are just **reinforcing IWY development**:

```javascript
// This is "IWY Testing" - an extension of IWY development
test('should create user', async () => {
  const user = await createUser('John', 'john@example.com');
  expect(user.name).toBe('John'); // Post-hoc "it works" confirmation
  expect(user.email).toBe('john@example.com'); // Direct value comparison for comfort
  expect(user.id).toBeDefined(); // 🤞 "Something exists" verification
});
```

**The core problem**: This isn't verifying constraints. It's **verifying results**.

### The Fundamental Problem of Design-less Testing

Most current testing approaches rely on **design-less dynamic comparison**:

- **No formal constraints**: No JSON Schema validation
- **No interface constraints**: No type constraints  
- **No hypermedia constraints**: Resource relationships ignored

```javascript
// Design-less testing
expect(response.body.user.profile.settings.theme).toBe('dark');
// ↑ Why this structure? Why this value? What are the constraints?
```

This is **verification of facts, not validation of design**.

We can confirm that the data "John" comes back, but we're not verifying **why it must be "John"** (the constraint).

### The Overlooked Hypermedia Constraints

A particularly problematic oversight is the absence of resource relationship verification:

```javascript
// IWY-style testing: completely ignoring link relationships
test('can purchase product', async () => {
  const product = await getProduct(123);
  const purchase = await purchaseProduct(123, userId); // Direct invocation
  expect(purchase.status).toBe('completed');
});
```

Product resources should link to purchase resources through **constraints**, but instead we're testing with direct invocation while **hypermedia constraints remain non-functional**.

**The proper constraint-driven approach**:
```php
// Constraint: Purchasable products provide purchase links
$product = $this->resource->get('app://self/products', ['id' => 123]);
$this->assertArrayHasKey('purchase', $product->body['_links']);

// Follow the constraint-defined link
$purchaseLink = $product->body['_links']['purchase'];
$purchase = $this->resource->post($purchaseLink['href'], $data);
```

## Practical Benefits: Why This Changes Everything

### 1. Hierarchical Constraint Testing: Mathematical Quality Assurance

Constraint-driven testing should be **hierarchical**:

```php
class UserConstraintTest extends TestCase
{
    // Level 1: Formal constraint verification (must pass first)
    public function testSchemaConstraints(): void
    {
        $validator = new JsonSchemaValidator();
        
        // Valid data satisfies constraints
        $validData = ['name' => 'John', 'email' => 'john@example.com'];
        $this->assertTrue($validator->validate($validData, 'user-create.json'));
        
        // Invalid data violates constraints
        $invalidData = ['name' => '', 'email' => 'invalid-email'];
        $this->assertFalse($validator->validate($invalidData, 'user-create.json'));
    }
    
    // Level 2: Interface constraint verification
    public function testInterfaceConstraints(): void
    {
        $query = $this->getInstance(UserQueryInterface::class);
        
        // Interface constraint: create must return int
        $userId = $query->create('John', 'john@example.com');
        $this->assertIsInt($userId);
        
        // Interface constraint: item must return specified array structure
        $user = $query->item($userId);
        $this->assertArrayHasKey('id', $user);
        $this->assertArrayHasKey('name', $user);
        $this->assertArrayHasKey('email', $user);
    }
    
    // Level 3: HTTP constraint verification
    public function testHttpConstraints(): void
    {
        $resource = $this->resource->post('app://self/users', $validData);
        
        // HTTP constraint: POST success is 201
        $this->assertSame(201, $resource->code);
        
        // REST constraint: created resources have Location header
        $this->assertArrayHasKey('Location', $resource->headers);
        
        // Resource constraint: self link existence
        $this->assertArrayHasKey('_links', $resource->body);
        $this->assertArrayHasKey('self', $resource->body['_links']);
    }
    
    // Level 4: Hypermedia constraint verification (critical!)
    public function testHypermediaConstraints(): void
    {
        $user = $this->resource->get('app://self/users', ['id' => 123]);
        
        // Hypermedia constraint: active users have edit links
        if ($user->body['status'] === 'active') {
            $this->assertArrayHasKey('edit', $user->body['_links']);
            $this->assertSame('PUT', $user->body['_links']['edit']['method']);
        }
        
        // Hypermedia constraint: disabled users have no edit links
        if ($user->body['status'] === 'disabled') {
            $this->assertArrayNotHasKey('edit', $user->body['_links']);
        }
    }
    
    // Level 5: Domain constraint verification (final layer)
    public function testDomainConstraints(): void
    {
        // Business rule: duplicate emails are not allowed
        $this->resource->post('app://self/users', ['name' => 'John', 'email' => 'test@example.com']);
        
        $duplicateResponse = $this->resource->post('app://self/users', ['name' => 'Jane', 'email' => 'test@example.com']);
        $this->assertSame(409, $duplicateResponse->code); // Conflict
    }
}
```

### The Fundamental Difference: IWY vs Constraint-Driven Testing

| Aspect | IWY Testing | Constraint-Driven Testing |
|--------|-------------|---------------------------|
| **Verification Target** | "It works" result facts | Constraint satisfaction logic |
| **Verification Method** | Direct value comparison | Constraint rule validation |
| **Hierarchy** | None | Formal→Type→HTTP→Hypermedia→Domain |
| **Predictability** | Low | High (deterministic by constraints) |
| **Maintainability** | Fragile (breaks on value changes) | Robust (auto-updates with constraint changes) |
| **AI Generation** | Difficult (unclear test intent) | Easy (auto-generated from constraints) |

### 2. Why Constraint-Driven Testing is Essential in the AI Era

Constraint-clear tests are **AI-generatable**:

```json
// Auto-generated tests from constraint definitions
{
  "user_creation": {
    "schema_constraint": "user-create.json",
    "http_constraint": {"success": 201, "failure": 400},
    "hypermedia_constraint": {"self_link": true, "edit_link": "if_active"},
    "business_constraint": {"unique_email": true}
  }
}
```

This **automatically generates**:
- Schema validation tests
- HTTP constraint tests  
- Hypermedia constraint tests
- Business rule validation tests
- Error case tests

**IWY tests** leave AI confused about what to generate because **constraints remain unclear**.

### 3. Predictable AI Generation

```php
// In screen-driven development, AI must guess:
// - What should this method return?
// - How should errors be handled?
// - What validation is needed?

// In constraint-driven development, AI knows:
#[JsonSchema('user-create.json')]  // Input constraints
public function onPost(string $name, string $email): static  // Type constraints
{
    $this->code = 201;  // HTTP constraints
    return $this;       // Resource constraints
}
```

Clear constraints lead to **deterministic AI generation** with 95%+ accuracy.

### 4. Change Impact Analysis

```
Semantic Change → Automatic constraint propagation → Minimal, targeted updates
```

When business rules change, the constraint system automatically identifies which layers are affected and suggests updates.

### 5. Team Communication

```
Traditional: "The user screen should show a list with pagination"
Constraint-driven: "The user resource supports safe list operations with range constraints"
```

Constraints become a **shared language** that eliminates ambiguity between team members.

### 6. Quality Assurance

```php
// Every constraint generates corresponding tests
class UserConstraintTest extends TestCase
{
    public function testHttpConstraints(): void
    {
        $resource = $this->resource->post('app://self/users', $validData);
        $this->assertSame(201, $resource->code);  // HTTP constraint
        $this->assertArrayHasKey('Location', $resource->headers);  // REST constraint
    }
    
    public function testSchemaConstraints(): void
    {
        $this->expectException(ValidationException::class);
        $this->resource->post('app://self/users', $invalidData);  // Schema constraint
    }
}
```

**100% test coverage** emerges naturally from constraint definitions.

## Implementation Strategy: Making the Transition

### Phase 1: Semantic Modeling

Start with your next feature. Before writing any code, ask:
- What are the core entities? (not "what screens do we need?")
- What operations are possible? (not "what buttons should we show?")
- What constraints govern these operations? (not "how should it look?")

### Phase 2: Constraint Definition

Define constraints explicitly before implementation:

```json
// user-operations.alps
{
  "descriptor": [
    {
      "id": "create_user",
      "type": "unsafe",
      "def": "Creates a new user account",
      "descriptor": [
        {"id": "name", "type": "semantic", "constraints": ["required", "max:255"]},
        {"id": "email", "type": "semantic", "constraints": ["required", "email", "unique"]}
      ]
    }
  ]
}
```

### Phase 3: Automated Generation

Use constraints to generate:
- Database schemas
- Validation rules
- API interfaces
- Test cases
- Documentation

### Phase 4: UI as Consumer

Build UI components that consume well-defined, constraint-driven APIs:

```javascript
// UI components become simple, focused, and reusable
const UserCreationForm = ({ onUserCreated }) => {
  const createUser = useResourceMutation('app://self/users', 'POST');
  
  return (
    <form onSubmit={createUser.mutate}>
      {/* Form reflects schema constraints, not arbitrary design decisions */}
      <input name="name" required maxLength={255} />
      <input name="email" type="email" required />
    </form>
  );
};
```

## The Future: Constraint-Driven Organizations

Imagine organizations where:

- **Product managers** define business capabilities, not screen layouts
- **Designers** create experiences around well-defined constraints
- **Developers** implement constraint satisfaction, not pixel-perfect mockups
- **QA engineers** verify constraint compliance, not manual workflows
- **DevOps** deploys constraint-validated systems with confidence

## Conclusion: Liberation Through Constraint

The paradox of constraint-driven development is that **constraints liberate**.

By defining clear boundaries—semantic, data, access, schema, HTTP, cross-cutting, and performance constraints—we create a system where:

- **Creativity flourishes** within well-defined bounds
- **Quality emerges** from constraint satisfaction
- **Change becomes manageable** through impact analysis
- **Collaboration improves** through shared constraint language
- **AI becomes powerful** through deterministic generation

We are not abandoning screens—we are **elevating them** from drivers to consumers. The screen becomes what it should have been all along: a **view** of our system's capabilities, not the definition of them.

This is more than a development methodology. This is a **fundamental reimagining** of how we think about software construction. From output-driven to capability-driven. From implementation-first to constraint-first. From chaos to **systematic, provable quality**.

The age of constraint-driven development has begun. The question is not whether this approach will dominate—it's how quickly we can learn to think this way.

---

*This article presents a new paradigm for software development that moves beyond traditional screen-driven approaches toward systematic, constraint-based construction. By making information design our Single Source of Truth, we create systems that are more robust, maintainable, and aligned with business intent.*