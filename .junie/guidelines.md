# Coding Guidelines

These guidelines apply to all code contributions in this repository.

## Testing Standards

### Test Method Naming
- All test methods **must** start with `it_` and read grammatically as a sentence
  - ✅ `it_creates_an_invoice_with_tax_included`
  - ❌ `testCreateInvoice`, `create invoice`, `get items`

### Test Structure: Arrange, Act, Assert (AAA)
Every test method must be structured with explicit AAA section comments using docblock style:
```php
#[Test]
public function it_creates_an_invoice(): void
{
    /* Arrange */
    $invoice = Invoice::factory()->raw([...]);

    /* Act */
    $response = $this->postJson('api/v1/invoices', $invoice);

    /* Assert */
    $response->assertOk();
    $this->assertDatabaseHas('invoices', [...]);
}
```

### PHPUnit Attributes
- All test methods **must** be annotated with `#[\PHPUnit\Framework\Attributes\Test]`
- Tests **must** be written as PHPUnit class-based tests (not Pest closures)
- Test classes must extend `Tests\TestCase`
- Use `use \Illuminate\Foundation\Testing\RefreshDatabase;` in all test classes

### PHPUnit Class Structure
```php
<?php

namespace Tests\Feature\Admin;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // shared setup here
    }

    #[Test]
    public function it_creates_an_invoice(): void
    {
        /* Arrange */
        /* Act */
        /* Assert */
    }
}
```

## Programming Principles

### SOLID
- **Single Responsibility**: Each class/method has one responsibility
- **Open/Closed**: Classes open for extension, closed for modification
- **Liskov Substitution**: Subtypes must be substitutable for their base types
- **Interface Segregation**: Clients should not depend on interfaces they don't use
- **Dependency Inversion**: Depend on abstractions, not concretions

### DRY (Don't Repeat Yourself)
- Extract shared logic into reusable methods or base classes
- Use `setUp()` for shared test setup instead of repeating in every test
- Avoid copy-pasting similar blocks; use loops or helpers

### Dynamic Programming
- Use data-driven loops when asserting over multiple similar cases:
```php
$documentTypes = [
    'invoice' => 'INV-000001',
    'estimate' => 'EST-000001',
];
foreach ($documentTypes as $key => $expected) {
    $this->getJson("api/v1/next-number?key={$key}")
        ->assertJson(['nextNumber' => $expected]);
}
```

### Early Returns
- Return or fail early to reduce nesting:
```php
if ($response->status() !== 200) {
    $this->fail('Expected 200, got: ' . $response->status());
}
```

## GitHub Actions / CI Workflows

- Workflows are **temporarily restricted to manual triggering only** (`workflow_dispatch`)
- `push` and `pull_request` triggers are commented out until stability is confirmed
- The `check.yaml` workflow supports a `fix_pint_errors` boolean input:
  - When `true`: runs `./vendor/bin/pint --fix` to auto-fix style issues
  - When `false` (default): runs `./vendor/bin/pint --test` to validate style

## AI Assistant CLI Restrictions

- **Do NOT run CLI commands** such as `php artisan test`, `composer install`, `npm install`, or any other shell/terminal commands
- Suggest commands for the developer to run instead of executing them directly
