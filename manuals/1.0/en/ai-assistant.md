---
layout: docs-en
title: BEAR.Sunday AI Assistant
category: Manual
permalink: /manuals/1.0/en/ai-assistant.html
---

# BEAR.Sunday AI Assistant

Discover resources to support BEAR.Sunday development with the power of AI.

## OpenAI GPTs - BEAR.Sunday Assistant

[BEAR.Sunday Assistant](https://chatgpt.com/g/g-67da572ba12c8191a4f85a88942d50f0-bear-sunday-assistant) is a custom GPT trained to specifically answer questions about the BEAR.Sunday framework.

<div class="info-box">
  <p><strong>Note:</strong> An OpenAI Plus account is required to use GPTs.</p>
</div>

## AI Support with llms-full.txt

To provide other AI assistants (such as Claude, Gemini, etc.) with knowledge of BEAR.Sunday, copy the contents of llms-full.txt using the button below, or retrieve it from [/llms-full.txt](/llms-full.txt) , and paste it at the beginning of your conversation with the AI.

### What is llms.txt?

The `llms-full.txt` file is based on the `llms.txt` standard, a simple way to share key information with AI models in a clean, Markdown format. It helps AI assistants quickly understand important details—like BEAR.Sunday’s framework—without clutter. Learn more at [llmstxt.org](https://llmstxt.org/).

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
