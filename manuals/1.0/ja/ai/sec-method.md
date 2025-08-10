---
layout: docs-ja
title: The SEC Method - Semantics → Experience → Constraints
category: AI
permalink: /manuals/1.0/ja/ai/sec-method.html
---

# The SEC Method: Semantics → Experience → Constraints

## Beyond Abstract Design: Discovering Constraints Through Reality

Traditional software design approaches often start with abstract constraints that may not reflect real-world usage. The **SEC Method** (Semantics → Experience → Constraints) revolutionizes this by grounding constraint discovery in actual user experience.

## The Problem with Abstract-First Design

### Typical Flawed Approach
```
Abstract Requirements → Arbitrary Constraints → Implementation → "Why doesn't this work?"
```

**Example of Abstract-First Failure:**
```json
// Abstract constraint definition
{
  "productName": {
    "type": "string",
    "maxLength": 255
  }
}
```

**Reality Check:** Where did 255 come from? Database limitation? Random number? This constraint has no experiential basis.

### The Hidden Costs
- **Unusable interfaces**: Constraints that break UX
- **Arbitrary limitations**: Rules without real justification  
- **Constant revision**: "Why did we limit this to 50 characters again?"
- **Stakeholder conflicts**: "But we need longer descriptions!"

## The SEC Method: A Revolutionary Approach

### SEC Flow Overview
```
Semantics (Meaning) → Experience (Reality) → Constraints (Rules)
```

### Phase 1: Semantic Definition
Start with **meaningful descriptions**, not technical specifications.

```json
// ALPS semantic descriptor
{
  "id": "productName", 
  "type": "semantic",
  "def": "https://schema.org/name",
  "title": "Product Name",
  "doc": "The commercial name by which a product is known to customers"
}
```

**Key Point**: Define the **meaning** and **purpose**, not the implementation.

### Phase 2: Experience Generation
Generate **massive realistic data** from semantic definitions.

```javascript
// Semantic-driven fake data generation
const productNames = [
  "Apple MacBook Pro 16-inch",
  "Sony WH-1000XM5 Wireless Noise Canceling Headphones", 
  "The Complete Works of William Shakespeare (Leather-bound Collector's Edition)",
  "Portable Solar Panel Charger for Outdoor Adventures and Emergency Backup Power",
  "Ultra-Premium Organic Cold-Pressed Extra Virgin Olive Oil from Ancient Groves"
];
```

**Experience Reality:**
- Some names are 20 characters
- Others are 80+ characters  
- Longest real-world names can exceed 200 characters
- UI breaks at different lengths depending on context

### Phase 3: Constraint Discovery
Derive **practical constraints** from observed experience.

```json
// Experience-informed constraints
{
  "productName": {
    "type": "string",
    "minLength": 1,
    "maxLength": 120,  // Based on UI testing with real data
    "pattern": "^[\\w\\s\\-.,()&]+$",  // Characters that actually work in practice
    "description": "Product name that fits in standard UI layouts and search results"
  }
}
```

**Evidence-Based Decisions:**
- 120 chars: Fits in mobile product cards
- Pattern: Supports real product naming conventions
- Constraints serve actual UX needs

## SEC Method in Practice

### Case Study: E-commerce Product System

#### Traditional Abstract Approach
```json
{
  "product": {
    "name": {"type": "string", "maxLength": 50},
    "description": {"type": "string", "maxLength": 200},
    "price": {"type": "number", "minimum": 0}
  }
}
```

**Problems Discovered:**
- Product names truncated: "Apple MacBook Pro 16-in..."
- Descriptions too short for detailed items
- Price range ignores currency formatting issues

#### SEC Method Application

**Phase 1: Semantics**
```json
{
  "alps": {
    "descriptor": [
      {"id": "productName", "def": "https://schema.org/name"},
      {"id": "productDescription", "def": "https://schema.org/description"},
      {"id": "price", "def": "https://schema.org/price"}
    ]
  }
}
```

**Phase 2: Experience**
Generate 1000+ realistic products:
- Electronics with model numbers
- Books with long subtitles
- Luxury items with elaborate descriptions
- International products with various currencies

**Phase 3: Constraints**
```json
{
  "product": {
    "name": {
      "type": "string", 
      "minLength": 5,
      "maxLength": 120,  // Accommodates real product names
      "examples": ["iPhone 15 Pro Max", "The Lord of the Rings: The Fellowship of the Ring"]
    },
    "description": {
      "type": "string",
      "minLength": 20,
      "maxLength": 2000,  // Based on actual product descriptions
      "examples": ["High-performance laptop with M3 chip..."]
    },
    "price": {
      "type": "object",  // Complex type discovered through experience
      "properties": {
        "amount": {"type": "number", "minimum": 0.01},
        "currency": {"type": "string", "enum": ["USD", "EUR", "JPY"]},
        "display": {"type": "string"}  // For formatted display like "$1,299.99"
      }
    }
  }
}
```

## SEC vs Traditional Approaches

| Aspect | Abstract-First | SEC Method |
|--------|----------------|------------|
| **Starting Point** | Arbitrary rules | Semantic meaning |
| **Validation** | Hope and prayer | Experiential evidence |
| **Flexibility** | Rigid, breaks easily | Adapts to reality |
| **Stakeholder Buy-in** | "Why this limit?" | "We tested this" |
| **Maintenance** | Constant revision | Stable, evidence-based |
| **User Experience** | Often poor fit | Naturally optimized |

## Benefits of the SEC Method

### 1. Evidence-Based Constraints
Every constraint has a **documented reason** based on real usage.

```json
{
  "userBio": {
    "maxLength": 160,  // Twitter-style bio that fits in profile cards
    "reason": "Testing showed longer bios break mobile layouts"
  }
}
```

### 2. Stakeholder Confidence
Business stakeholders see **why** constraints exist through prototypes.

**Before**: "Why can't product names be longer?"
**After**: "Oh, I see how it breaks the layout. 120 characters makes sense."

### 3. Future-Proof Design
Constraints based on **semantic meaning** adapt better than arbitrary technical limits.

### 4. AI-Friendly
Clear semantics → abundant training data → better AI assistance.

```javascript
// AI can generate better constraints from rich semantic context
const constraintPrompt = `
Based on the semantic definition of "productName" as a commercial identifier
that appears in search results, shopping carts, and product catalogs,
generate appropriate validation constraints.
`;
```

## Implementation Strategy

### Step 1: Semantic Modeling
Define what things **mean**, not how they're implemented.

```json
{
  "customerReview": {
    "type": "semantic",
    "def": "https://schema.org/Review",
    "doc": "Customer feedback about product experience and quality"
  }
}
```

### Step 2: Experience Generation
Create **realistic, diverse scenarios**.

```javascript
// Generate reviews of varying lengths and styles
const reviews = [
  "Great product!",  // Short and sweet
  "I've been using this for 6 months and it's completely transformed my workflow. The build quality is exceptional, customer service is responsive, and it integrates perfectly with my existing tools. Highly recommended for professionals.",  // Detailed experience
  "Meh. It's okay I guess. Works as advertised but nothing special. Probably wouldn't buy again but not bad enough to return."  // Mixed/negative
];
```

### Step 3: Constraint Discovery
Let **reality inform rules**.

```json
{
  "customerReview": {
    "type": "string",
    "minLength": 10,   // Filters out useless "Great!" reviews
    "maxLength": 2000, // Accommodates detailed experiences
    "pattern": "^[^<>{}]+$",  // Prevents HTML injection
    "description": "Customer review that provides meaningful feedback"
  }
}
```

## The SEC Mindset Shift

### From Questions Like:
- "What's a reasonable character limit?"
- "How long should descriptions be?"
- "What validation rules should we add?"

### To Questions Like:
- "What does this represent in the real world?"
- "How do people actually use this?"
- "What breaks when we test with realistic data?"

## Conclusion: Grounding Design in Reality

The SEC Method transforms software design from **assumption-driven** to **evidence-driven**.

By starting with semantic meaning, experiencing realistic scenarios, and discovering constraints through observation, we create systems that **naturally fit** how they'll actually be used.

**SEC isn't just a methodology—it's a mindset shift toward grounding our digital creations in human reality.**

The next time you're tempted to write `"maxLength": 255`, ask yourself: **What would the SEC Method discover?**

---

*The SEC Method emerged from the constraint-driven development analysis and represents a practical approach to discovering meaningful constraints through experiential validation rather than abstract speculation.*