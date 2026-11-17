---
layout: docs-en
title: BEAR.Sunday AI Assistant
category: Manual
permalink: /manuals/1.0/en/ai-assistant.html
---

# BEAR.Sunday AI Assistant

AI is no longer just answering questions. In the agent era, AI generates code following BEAR.Sunday conventions, reviews it, and helps all the way through to implementation. Here are two essential resources: [BEAR.Skills](https://github.com/bearsunday/BEAR.Skills) and [BEAR.Kata](https://github.com/bearsunday/BEAR.Kata).

## Implement with Agents

### BEAR.Skills

> Stop coding blind. Just ask your AI.

[BEAR.Skills](https://github.com/bearsunday/BEAR.Skills) is a collection of skills for Claude Code. It automatically selects the appropriate skill from natural language instructions in English or Japanese, then generates code, reviews quality, and enforces conventions.

Install via the Claude Code marketplace:

```
/plugin marketplace add bearsunday/BEAR.Skills
/plugin install bear-skills
```

(Update with `/plugin update bear-skills`, uninstall with `/plugin uninstall bear-skills`)

After installation, simply ask naturally in your project—"create a resource," "review this,"—and the appropriate skill runs. You can also call skills directly with commands like `/bear-review`.

Representative skills:

| Skill | Role |
|---|---|
| `bear-review` | Quality review |
| `bear-resource-gen` | Resource generation |
| `bear-hypermedia` | Hypermedia-enable |
| `bear-cacheable` | Add caching |
| `bear-security-setup` | Security setup |

Skills are continuously added and updated. See [BEAR.Skills](https://github.com/bearsunday/BEAR.Skills) for the complete list and latest information.

### BEAR.Kata

[BEAR.Kata](https://github.com/bearsunday/BEAR.Kata) is an implementation pattern and reference collection that helps both AI agents and humans quickly find "which files to look at" when implementing a feature. It contains over 60 Kata (reusable implementation patterns) for databases, APIs, HTML, authentication, caching, testing, and more.

You can browse by feature index or keyword. When you tell Claude Code "implement ◯◯ following kata," the `bear-kata` skill guides you to the matching pattern.

## Make Your App AI-Accessible

So far we’ve covered how AI helps you develop. Now, the reverse: how to make your application itself usable by AI agents.

### BEAR.ToolUse

> Automatically generates Tool Use definitions from resource classes and manages the agent loop with LLMs.

[BEAR.ToolUse](https://github.com/bearsunday/BEAR.ToolUse) automatically generates JSON Schema-based tool definitions just by adding `#[Tool]` to resource class methods, then runs the LLM agent loop (tool call → feedback → next action). It includes human-in-the-loop confirmation for destructive operations, streaming, and response filtering to save tokens. Because the LLM API implementation is swappable, it’s LLM-agnostic. Following HTTP and CLI, it lets you expose resources directly as tools to LLMs.

### BEAR.Mcp (Community)

[BEAR.Mcp](https://github.com/NaokiTsuchiya/BEAR.Mcp) is a community effort that exposes BEAR.Sunday apps as MCP (Model Context Protocol) servers. Methods marked with `#[Mcp]` become tools, providing a "third protocol binding" after HTTP and CLI, inheriting existing HTTP semantics, documentation, and schema metadata.

## Share Knowledge with AI

When you’re not using agents or conversing with other AI assistants, you can provide BEAR.Sunday context in these ways.

### OpenAI GPTs - BEAR.Sunday Assistant

[BEAR.Sunday Assistant](https://chatgpt.com/g/g-67da572ba12c8191a4f85a88942d50f0-bear-sunday-assistant) is a custom GPT trained to specifically answer questions about the BEAR.Sunday framework.

<div class="info-box">
  <p><strong>Note:</strong> An OpenAI Plus account is required to use GPTs.</p>
</div>

### llms-full.txt

To provide other AI assistants (such as Claude, Gemini, etc.) with knowledge of BEAR.Sunday, copy the contents of llms-full.txt using the button below, or retrieve it from [/llms-full.txt](/llms-full.txt), and paste it at the beginning of your conversation.

The `llms-full.txt` file follows the [llms.txt standard](https://llmstxt.org/), a simple way to share key information with AI models in clean Markdown format. This helps AI assistants quickly understand BEAR.Sunday’s important details without clutter.

<button id="copyLlmsText" class="copy-button">Copy llms-full.txt</button>
<span id="copyStatus" class="copy-status"></span>

<div class="usage-guide">
  <h3>How to Use the Copied Information</h3>
  <ul>
    <li><strong>Claude:</strong> Paste at the beginning of your conversation or upload as a Project</li>
    <li><strong>Other AI Assistants:</strong> Paste at the beginning of your conversation with a note: "This is information about BEAR.Sunday. Please understand this information before answering my questions"</li>
  </ul>
  <p>※ If the AI assistant doesn't have prior knowledge of BEAR.Sunday, be sure to provide this information.</p>
</div>

---

<script>
document.getElementById('copyLlmsText').addEventListener('click', function() {
  // Fetch the llms-full.txt file from the root
  fetch('/llms-full.txt')
    .then(response => {
      if (!response.ok) {
        throw new Error('File not found');
      }
      return response.text();
    })
    .then(text => {
      navigator.clipboard.writeText(text).then(function() {
        const status = document.getElementById('copyStatus');
        status.textContent = 'Copied!';
        setTimeout(function() {
          status.textContent = '';
        }, 2000);
      }).catch(function(err) {
        console.error('Failed to copy to clipboard', err);
        alert('Failed to copy to clipboard.');
      });
    })
    .catch(error => {
      console.error('Failed to load file:', error);
      alert('Failed to load llms-full.txt.');
    });
});
</script>

<style>
.info-box {
  background-color: #f8f9fa;
  border-left: 4px solid #17a2b8;
  padding: 15px;
  margin: 20px 0;
  border-radius: 4px;
}

.usage-guide {
  background-color: #fff3cd;
  border-left: 4px solid #ffc107;
  padding: 15px;
  margin: 20px 0;
  border-radius: 4px;
}

.usage-guide h3 {
  margin-top: 0;
  color: #856404;
}

.copy-button {
  background-color: #4CAF50;
  border: none;
  color: white;
  padding: 10px 20px;
  text-align: center;
  text-decoration: none;
  display: inline-block;
  font-size: 16px;
  margin: 4px 2px;
  cursor: pointer;
  border-radius: 4px;
  transition: background-color 0.3s;
}

.copy-button:hover {
  background-color: #45a049;
}

.copy-status {
  margin-left: 10px;
  color: #4CAF50;
  font-weight: bold;
}
</style>
