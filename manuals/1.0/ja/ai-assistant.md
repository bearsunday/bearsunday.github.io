---
layout: docs-ja
title: BEAR.Sunday AI アシスタント
category: Manual
permalink: /manuals/1.0/ja/ai-assistant.html
---

# BEAR.Sunday AI アシスタント

AIのパワーを活用してBEAR.Sunday開発をサポートするリソースを見つけましょう。

## OpenAI GPTs - BEAR.Sunday アシスタント

[BEAR.Sunday アシスタント](https://chatgpt.com/g/g-67da572ba12c8191a4f85a88942d50f0-bear-sunday-assistant)は、BEAR.Sundayフレームワークに関する質問に特化して回答するためにトレーニングされたカスタムGPTです。

<div class="info-box">
  <p><strong>注意:</strong> GPTsを使用するにはOpenAI Plus アカウントが必要です。</p>
</div>

## llms-full.txtを使ったAIサポート

他のAIアシスタント（Claude、Geminiなど）にBEAR.Sundayの知識を提供するには、以下のボタンを使用してllms-full.txtの内容をコピーするか[/llms-full.txt](/llms-full.txt)から取得して、AIとの会話の冒頭に貼り付けてください。

### llms.txtとは？

`llms-full.txt`ファイルは、`llms.txt`標準に基づいています。これはAIモデルと重要な情報を簡潔なMarkdown形式で共有するシンプルな方法です。これにより、AIアシスタントはBEAR.Sundayフレームワークなどの重要な詳細を余計な情報なしで素早く理解できます。詳細は[llmstxt.org](https://llmstxt.org/)をご覧ください。

<button id="copyLlmsText" class="copy-button">llms-full.txtをコピー</button>
<span id="copyStatus" class="copy-status"></span>

<div class="usage-guide">
  <h3>コピーした情報の使い方</h3>
  <ul>
    <li><strong>Claude:</strong> 会話の冒頭に貼り付けるか、プロジェクトとしてアップロードしてください</li>
    <li><strong>その他のAIアシスタント:</strong> 会話の冒頭に貼り付け、「これはBEAR.Sundayに関する情報です。質問に答える前にこの情報を理解してください」というメモを添えてください</li>
  </ul>
  <p>※ AIアシスタントがBEAR.Sundayに関する事前知識を持っていない場合は、必ずこの情報を提供してください。</p>
</div>

---

<script>
document.getElementById('copyLlmsText').addEventListener('click', function() {
  // Fetch the llms-full.txt file from the root
  fetch('/llms-full.txt')
    .then(response => {
      if (!response.ok) {
        throw new Error('ファイルが見つかりません');
      }
      return response.text();
    })
    .then(text => {
      navigator.clipboard.writeText(text).then(function() {
        const status = document.getElementById('copyStatus');
        status.textContent = 'コピーしました！';
        setTimeout(function() {
          status.textContent = '';
        }, 2000);
      }).catch(function(err) {
        console.error('クリップボードへのコピーに失敗しました', err);
        alert('クリップボードへのコピーに失敗しました。');
      });
    })
    .catch(error => {
      console.error('ファイルの読み込みに失敗しました:', error);
      alert('llms-full.txtの読み込みに失敗しました。');
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
