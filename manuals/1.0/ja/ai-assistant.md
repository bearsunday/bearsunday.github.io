---
layout: docs-ja
title: BEAR.Sunday AI アシスタント
category: Manual
permalink: /manuals/1.0/ja/ai-assistant.html
---

# BEAR.Sunday AI アシスタント

AIは質問に答えるだけの存在ではなくなりました。エージェント時代のAIは、BEAR.Sundayの規約に沿ってコードを生成し、レビューし、"実装まで"手伝います。そのための2つのリソース、[BEAR.Skills](https://github.com/bearsunday/BEAR.Skills)と[BEAR.Kata](https://github.com/bearsunday/BEAR.Kata)を紹介します。

## エージェントで実装する

### BEAR.Skills

> Stop coding blind. Just ask your AI.

[BEAR.Skills](https://github.com/bearsunday/BEAR.Skills)はClaude Code向けのスキル集です。英語・日本語の自然な指示から該当スキルを自動選択し、コード生成・品質レビュー・規約強制を行います。

Claude Codeのマーケットプレイス経由でインストールします。

```
/plugin marketplace add bearsunday/BEAR.Skills
/plugin install bear-skills
```

（更新は `/plugin update bear-skills`、削除は `/plugin uninstall bear-skills`）

インストール後は、プロジェクト内で「リソースを作って」「レビューして」と自然に頼むだけで該当スキルが動きます。`/bear-review` のようにコマンドで直接呼ぶこともできます。

代表的なスキル:

| スキル | 役割 |
|---|---|
| `bear-review` | 品質レビュー |
| `bear-resource-gen` | リソース生成 |
| `bear-hypermedia` | ハイパーメディア化 |
| `bear-cacheable` | キャッシュ化 |
| `bear-security-setup` | セキュリティ設定 |

スキルは随時追加・更新されます。全スキルの一覧と最新情報は [BEAR.Skills](https://github.com/bearsunday/BEAR.Skills) を参照してください。

### BEAR.Kata

[BEAR.Kata](https://github.com/bearsunday/BEAR.Kata)は「これを実装したい時、どのファイルを見るか」をAIエージェントと人間が素早く引くための実装パターン・リファレンス集です。DB・API・HTML・認証・キャッシュ・テストなど、60以上のKata（再利用可能な実装パターン）を収録しています。

機能別の索引やキーワードで引けるほか、Claude Codeに「◯◯をkataに従って実装して」と頼むと`bear-kata`スキルが該当パターンへ誘導します。

## アプリをAIエージェントにする

ここまでは「AIが開発を助ける」側の話でした。ここからは逆に、あなたのアプリケーション自体をAIエージェントから使えるようにする方法です。

### BEAR.ToolUse

> Automatically generates Tool Use definitions from resource classes and manages the agent loop with LLMs.

[BEAR.ToolUse](https://github.com/bearsunday/BEAR.ToolUse)は、リソースクラスのメソッドに `#[Tool]` を付けるだけで、JSON Schemaベースのツール定義を自動生成し、LLMのエージェントループ（ツール呼び出し→結果フィードバック→次の行動）を回します。破壊的操作のhuman-in-the-loop確認、ストリーミング、トークンを節約するレスポンスフィルタを備え、LLM API は実装を差し替えられるためLLM非依存です。HTTP・CLIに続く形で、リソースをそのままLLMから呼べるツールにします。

### BEAR.Mcp（コミュニティ）

[BEAR.Mcp](https://github.com/NaokiTsuchiya/BEAR.Mcp)は、BEAR.SundayアプリをMCP（Model Context Protocol）サーバーとして公開するコミュニティによる取り組みです。`#[Mcp]` を付けたリソースメソッドをツールとして提供し、HTTP・CLIに次ぐ「第3のプロトコル束縛」として、既存のHTTPセマンティクス・ドキュメント・スキーマからメタデータを引き継ぎます。

## AIに知識を渡す

エージェントを使わない場合や、他のAIアシスタントと会話する場合は、BEAR.Sundayの事前知識を渡す次の方法があります。

### OpenAI GPTs - BEAR.Sunday アシスタント

[BEAR.Sunday アシスタント](https://chatgpt.com/g/g-67da572ba12c8191a4f85a88942d50f0-bear-sunday-assistant)は、BEAR.Sundayフレームワークに関する質問に特化して回答するためにトレーニングされたカスタムGPTです。

<div class="info-box">
  <p><strong>注意:</strong> GPTsを使用するにはOpenAI Plus アカウントが必要です。</p>
</div>

### llms-full.txt

AIアシスタント（Claude、Geminiなど）にBEAR.Sundayの知識を提供するには、以下のボタンを使用してllms-full.txtの内容をコピーするか[/llms-full.txt](/llms-full.txt)から取得して、AIとの会話の冒頭に貼り付けてください。

`llms-full.txt`は[llms.txt標準](https://llmstxt.org/)に基づくファイルで、AIモデルと重要な情報を簡潔なMarkdown形式で共有するシンプルな方法です。AIアシスタントはBEAR.Sundayの重要な詳細を余計な情報なしで素早く理解できます。

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
