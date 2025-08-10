---
layout: docs-ja
title: 制約駆動開発マニフェスト
category: AI
permalink: /manuals/1.0/ja/ai/constraint-driven-manifesto.html
---

# 制約駆動開発マニフェスト

## はじめに：開発パラダイムの転換

従来のソフトウェア開発は「**画面から作る**」発想でした。PMから画面仕様をもらい、それに合わせてコードを書く。しかし、BEAR.Sundayが提示する**制約駆動開発**は、この発想を根本から覆します。

私たちは、**要素を制約で構成する**ことで、より堅牢で保守性の高いシステムを構築します。

## 制約駆動開発の基本原則

### 原則1: 画面ではなく要素から始める

```
❌ 従来の開発: 画面仕様 → 実装 → テスト
✅ 制約駆動開発: 要素抽出 → 制約定義 → 自動構成
```

**従来のアプローチ:**
```
PM: 「ユーザー一覧画面を作ってください。検索機能とページング機能付きで」
開発者: 「了解しました」→ HTMLテンプレート作成 → コントローラー作成 → ...
```

**制約駆動アプローチ:**
```
要素抽出: [User, Search, Pagination, Display]
制約定義: User(REST制約) + Search(Query制約) + Pagination(Range制約) + Display(View制約)
自動構成: 制約から実装が自動導出される
```

### 原則2: 制約の階層化による品質保証

```
セマンティック制約 → データ制約 → アクセス制約 → 入出力制約 → HTTP制約 → 横断制約 → キャッシュ制約
```

各制約層が次の層を制約することで、**段階的品質向上**を実現します。

### 原則3: 制約違反の即座検出

```php
// 制約違反は開発時に即座に検出される
class User extends ResourceObject
{
    public function onPost(string $name): static  // 型制約
    {
        if (strlen($name) === 0) {  // ❌ これはSchema制約で事前検出すべき
            throw new ValidationException();
        }
        // ...
    }
}
```

正しい制約駆動アプローチ:
```php
class User extends ResourceObject
{
    #[JsonSchema('user-create.json')]  // Schema制約で事前検証
    public function onPost(string $name): static
    {
        // 制約を満たした $name のみが到達
        $this->code = 201;
        return $this;
    }
}
```

## 制約駆動開発プロセス

### Phase 1: 要素の抽出と分類

#### 1.1 ビジネス要素の抽出
```
要求: "顧客がECサイトで商品を検索し、カートに追加し、購入する"

要素抽出:
- Customer (顧客)
- Product (商品)  
- Search (検索)
- Cart (カート)
- Purchase (購入)
- Payment (決済)
```

#### 1.2 要素の分類と関係定義
```
Entity要素: Customer, Product, Cart, Order
Action要素: Search, AddToCart, Purchase, Payment
Query要素: ProductSearch, CartView, OrderHistory
Command要素: AddProduct, UpdateCart, ProcessPayment
```

### Phase 2: 制約の定義と階層化

#### 2.1 セマンティック制約 (ALPS)
```json
{
  "alps": {
    "descriptor": [
      {
        "id": "product",
        "type": "semantic",
        "def": "商品リソース",
        "descriptor": [
          {"id": "name", "type": "semantic", "def": "商品名"},
          {"id": "price", "type": "semantic", "def": "価格"}
        ]
      },
      {
        "id": "search",
        "type": "safe",
        "rt": "product",
        "def": "商品検索操作"
      }
    ]
  }
}
```

#### 2.2 データ制約 (SQL)
```sql
-- 制約: ALPS記述から自動生成
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL CHECK (price >= 0),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### 2.3 アクセス制約 (Interface)
```php
interface ProductQueryInterface
{
    /**
     * @return array{id: int, name: string, price: float}
     */
    #[DbQuery('product_item', 'sql/product_item.sql')]
    public function item(int $id): array;
}
```

#### 2.4 入出力制約 (JSON Schema)
```json
{
  "type": "object",
  "properties": {
    "name": {"type": "string", "minLength": 1, "maxLength": 255},
    "price": {"type": "number", "minimum": 0}
  },
  "required": ["name", "price"]
}
```

#### 2.5 HTTP制約 (Resource)
```php
class Products extends ResourceObject
{
    #[JsonSchema('product-create.json')]
    public function onPost(string $name, float $price): static
    {
        $this->code = 201;  // HTTP制約: 作成成功
        $this->headers['Location'] = "/products/{$id}";  // HTTP制約: 作成リソースの位置
        return $this;
    }
}
```

### Phase 3: 制約の自動検証と統合

#### 3.1 制約整合性の検証
```php
// 全制約層の整合性を自動検証
class ConstraintValidator
{
    public function validateConstraintHierarchy(): void
    {
        $this->validateAlpsToSqlMapping();      // ALPS ↔ SQL
        $this->validateSqlToInterfaceMapping(); // SQL ↔ Interface  
        $this->validateInterfaceToSchemaMapping(); // Interface ↔ Schema
        $this->validateSchemaToResourceMapping();  // Schema ↔ Resource
    }
}
```

#### 3.2 制約違反の自動修正
```php
// 制約違反が検出された場合の自動修正提案
if ($constraint_violation_detected) {
    throw new ConstraintViolationException(
        "Schema制約でprice.minimumが0だが、SQL制約でCHECK (price >= 1)が定義されています。" .
        "修正案: Schema制約をminimum: 1に変更してください。"
    );
}
```

### Phase 4: 実装の自動生成

#### 4.1 テストの自動生成
```php
// 制約から自動生成されるテスト
class ProductConstraintTest extends TestCase
{
    public function testSchemaConstraints(): void
    {
        $validator = new JsonSchemaValidator();
        
        // 有効データのテスト
        $validData = ['name' => 'Product A', 'price' => 100.0];
        $this->assertTrue($validator->validate($validData, 'product-create.json'));
        
        // 制約違反のテスト
        $invalidData = ['name' => '', 'price' => -1];
        $this->assertFalse($validator->validate($invalidData, 'product-create.json'));
    }
}
```

#### 4.2 実装の自動生成
```php
// 制約定義から自動生成される実装
class Products extends ResourceObject
{
    // 自動生成: Schema制約
    #[JsonSchema('product-create.json')]
    
    // 自動生成: HTTP制約
    public function onPost(string $name, float $price): static
    {
        // 自動生成: ステータス制約
        $this->code = 201;
        
        // 自動生成: ヘッダー制約
        $this->headers['Location'] = "/products/{$id}";
        
        // 自動生成: データアクセス
        $this->body = ['id' => $this->query->create($name, $price)];
        
        return $this;
    }
}
```

## 制約駆動開発の利点

### 1. 品質の数学的保証

```
∀ constraint ∈ ConstraintSet: 
  satisfy(constraint) = true → Quality(System) = Guaranteed
```

制約をすべて満たすシステムは、**数学的に品質が保証**されます。

### 2. 変更影響の局所化

```
要求変更 → 制約変更 → 影響範囲の自動特定 → 部分修正
```

制約システムにより、変更の影響範囲が**自動的に特定**され、修正範囲が最小化されます。

### 3. AI/自動化との親和性

```php
// 制約が明確 → AI生成の品質向上
$constraints = [
    'alps' => $alps_definition,
    'sql' => $sql_constraints, 
    'schema' => $json_schema,
    'http' => $rest_constraints
];

$ai_generated_code = $ai->generate($constraints);  // 高精度で生成可能
```

明確な制約により、AIによる自動生成の**精度と品質が飛躍的に向上**します。

### 4. チーム開発の標準化

```
制約定義 = 開発チーム間の共通言語
```

制約により、開発チーム間の**認識統一**と**作業分担**が明確になります。

## 従来手法との比較

| 観点 | 画面駆動開発 | 制約駆動開発 |
|------|-------------|-------------|
| **開始点** | PM仕様書・画面モック | 要素抽出・制約定義 |
| **品質保証** | 手動テスト中心 | 制約による自動保証 |
| **変更対応** | 全体への波及影響 | 局所化された影響 |
| **AI活用** | 低精度（曖昧な仕様） | 高精度（明確な制約） |
| **チーム協業** | 個人依存 | 制約による標準化 |
| **保守性** | 劣化傾向 | 制約により維持 |

## 実践指針

### 開発者向け指針

#### 1. 「画面を見るな、要素を見よ」
```
❌ 「ログイン画面を作る」
✅ 「User認証要素 + Session管理要素 + Security制約を構成する」
```

#### 2. 「実装する前に制約を定義せよ」
```php
// ❌ いきなり実装
class Login extends ResourceObject
{
    public function onPost($email, $password) {  // 型不明、制約不明
        // いきなり実装...
    }
}

// ✅ 制約を先に定義
#[JsonSchema('login.json')]  // 入力制約
class Login extends ResourceObject
{
    public function onPost(string $email, string $password): static  // 型制約
    {
        // 制約を満たした実装
    }
}
```

#### 3. 「制約違反を恐れるな、活用せよ」
```php
// 制約違反は品質改善の機会
try {
    $this->validateConstraints($data);
} catch (ConstraintViolationException $e) {
    // 制約違反 = 設計の改善点の発見
    $this->improveConstraints($e->getViolatedConstraints());
}
```

### チーム向け指針

#### 1. 制約定義の共同作業
```
要求分析 → 要素抽出(全員) → 制約定義(合意) → 実装(分担)
```

#### 2. 制約レビューの義務化
```
コードレビュー = 実装レビュー + 制約レビュー
```

#### 3. 制約進化の管理
```
制約変更 = 全体影響分析 + 段階的移行計画
```

## 結論：制約が自由を生む

制約駆動開発は、一見すると「制限」に見えるかもしれません。しかし、実際には：

### 制約により得られる自由
1. **実装の自由**: 制約を満たす限り、実装方法は自由
2. **変更の自由**: 制約の範囲内での変更は影響が限定的  
3. **協業の自由**: 制約により分業・並行作業が可能
4. **品質の自由**: 制約により品質を気にせず開発に集中

### 制約駆動開発の真価

```
制約 ≠ 束縛
制約 = 創造的制限による品質保証システム
```

**制約駆動開発**は、単なる開発手法ではありません。これは、ソフトウェア工学における**品質保証パラダイムの革命**なのです。

---

*この制約駆動開発マニフェストは、BEAR.Sundayの制約システムを通じて実現される新しい開発パラダイムを提示しています。従来の「画面から作る」発想から「要素を制約で構成する」発想への転換により、より堅牢で保守性の高いシステム開発が可能になります。*