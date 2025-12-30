---
layout: docs-en
title: Security
category: Manual
permalink: /manuals/1.0/en/security.html
---

# Security

Security tools can scan your application for vulnerability assessment. With static analysis, dynamic testing, taint analysis, and AI auditing, architecture-aware tools analyze from multiple angles, detecting vulnerabilities that generic tools miss.

## Installation

Install [bear/security](https://github.com/bearsunday/BEAR.Security).

```bash
composer require --dev bear/security
```

## Scanning Tools

| Tool | What it does | When to use |
|------|--------------|-------------|
| SAST[^sast] | Static analysis to find dangerous patterns in your code | During development |
| DAST[^dast] | Dynamic analysis to send attack requests to your app | Before deployment |
| AI Auditor | AI reviews your code for security issues | Code review |
| Psalm Plugin | Traces user input to dangerous operations | During development |

[^sast]: Static Application Security Testing
[^dast]: Dynamic Application Security Testing

## SAST

Scans your source code for dangerous patterns:

```bash
./vendor/bin/bear.security-scan src
```

Detects 14 vulnerability types:

| Category | Examples |
|----------|----------|
| Injection | SQL injection, Command injection, XSS |
| Access Control | Path traversal, Open redirect |
| Cryptography | Weak hash algorithms, Hardcoded secrets |
| Data Protection | Insecure deserialization, XXE |
| Session | Session fixation, CSRF |
| Network | SSRF, Remote file inclusion |

See the [Vulnerability Reference](https://bearsunday.github.io/BEAR.Security/issues/en/) for details on each vulnerability.

## DAST

Sends attack payloads to your running application to test real vulnerabilities:

```bash
./vendor/bin/bear-security-dast 'MyVendor\MyApp' prod-app /path/to/app
```

Tests include:

| Test | What it sends |
|------|---------------|
| SQL Injection | `' OR '1'='1`, `; DROP TABLE` |
| XSS | `<script>alert(1)</script>` |
| Command Injection | `; ls -la`, `\| cat /etc/passwd` |
| Path Traversal | `../../../etc/passwd` |
| Security Headers | Checks for missing headers |

## AI Auditor

Uses Claude AI to find security issues that pattern matching cannot detect:

```bash
# Option 1: API Key
export ANTHROPIC_API_KEY=sk-ant-...
./vendor/bin/bear-security-audit src

# Option 2: Claude CLI (Max Plan - no API key required)
claude auth login
./vendor/bin/bear-security-audit src
```

| Issue | Description |
|-------|-------------|
| IDOR | Accessing other users' data without authorization check |
| Mass Assignment | Accepting unvalidated fields in updates |
| Race Condition | Time-of-check to time-of-use flaws |
| Business Logic | Application-specific security flaws |

## Psalm Plugin (Taint Analysis)

Taint analysis is a static analysis technique that marks user input as tainted variables and traces how that taint propagates through your code. It reports vulnerabilities when tainted data reaches SQL queries or HTML output without proper sanitization.

### Setup

Add the plugin and stubs to your `psalm.xml`:

```xml
<?xml version="1.0"?>
<psalm
    xmlns="https://getpsalm.org/schema/config"
    errorLevel="1"
>
    <projectFiles>
        <directory name="src"/>
    </projectFiles>
    <stubs>
        <file name="vendor/bear/security/stubs/AuraSql.phpstub"/>
        <file name="vendor/bear/security/stubs/PDO.phpstub"/>
        <file name="vendor/bear/security/stubs/Qiq.phpstub"/>
    </stubs>
    <plugins>
        <pluginClass class="BEAR\Security\Psalm\ResourceTaintPlugin">
            <targets>
                <target>Page</target>
                <target>App</target>
            </targets>
        </pluginClass>
    </plugins>
</psalm>
```

The `targets` specify which resources receive external input. Use `Page` when serving web pages with `html` context, `App` when serving APIs with `api` context.

### Stubs

Stubs provide taint annotations for third-party libraries:

| Stub | Purpose |
|------|---------|
| `AuraSql.phpstub` | Marks SQL query methods as taint sinks |
| `PDO.phpstub` | Marks PDO methods as taint sinks |
| `Qiq.phpstub` | Marks template output as taint sinks |

### Running

Run taint analysis:

```bash
./vendor/bin/psalm --taint-analysis
```

Add a convenience script to `composer.json`:

```json
{
    "scripts": {
        "taint": "./vendor/bin/psalm --taint-analysis 2>&1 | grep -E 'Tainted' || true"
    }
}
```

This filters output to show only taint errors.

Then run with:

```bash
composer taint
```

## GitHub Actions

Add security scanning to your CI pipeline:

```bash
cp vendor/bear/security/workflows/security-sast.yml .github/workflows/
```

This workflow runs on every push and pull request:

| Job | What it does |
|-----|--------------|
| SAST Scan | Scans code and uploads results to GitHub Security tab |
| Psalm Taint | Traces user input flows and uploads results to GitHub Security tab |

Results appear in your repository's **Security > Code scanning** section.

## Architecture and Security

BEAR.Sunday's architecture makes security scanning more effective:

- **Clear Entry Points**: Every endpoint is a ResourceObject with `onGet`, `onPost` methods. Scanners can identify all inputs and trace data flow.

- **No Hidden Magic**: Dependencies are explicit through constructor injection. Scanners can analyze the complete code path.

- **Framework-Aware AI**: The AI Auditor understands BEAR.Sunday patterns and can detect business logic flaws, not just generic vulnerabilities.

## Prompt for AI Agents

To set up bear/security with an AI coding assistant, use this prompt:

```
Follow the setup instructions at:
https://raw.githubusercontent.com/bearsunday/BEAR.Skills/1.x/.claude/skills/bear-security-setup/SKILL.md
```
