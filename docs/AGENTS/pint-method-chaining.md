# Pint Method Chaining Configuration

## Current Configuration

Pint is configured to properly format method chains that are already broken across multiple lines using the `method_chaining_indentation` rule.

**Configuration files:**
- `pint.json` - Main Pint configuration with `method_chaining_indentation: true`
- `.php-cs-fixer.dist.php` - PHP-CS-Fixer config with line length set to 100

## Important Limitation

**Pint (via PHP-CS-Fixer) does NOT automatically break long single-line method chains.**

Pint only formats method chains that are **already broken** across multiple lines. It will:
- ✅ Apply proper indentation to method chains that are already multi-line
- ✅ Maintain formatting of properly broken chains
- ❌ NOT automatically break long single-line chains

## Why This Limitation Exists

PHP-CS-Fixer (which Pint uses) is a code formatter, not a code transformer. It focuses on:
- Consistent spacing and indentation
- Code style rules (PSR-12, Laravel standards, etc.)
- Formatting code that's already structured correctly

Automatically breaking long lines would require understanding code semantics, which is beyond a formatter's scope.

## Solutions

### Option 1: IDE/Editor Configuration (Recommended)

Configure your IDE to automatically break long lines as you type or on save.

#### VS Code / Cursor

The `.vscode/settings.json` file is already configured. You may need to install the **PHP CS Fixer** extension:

1. Install: `junstyle.php-cs-fixer`
2. The extension will use Pint to format on save
3. However, it still won't auto-break long lines - you'll need to manually break them

**Better approach for VS Code:**

Use Intelephense with word wrap or manually break lines, then let Pint format:

```json
{
    "editor.rulers": [100],
    "[php]": {
        "editor.wordWrap": "wordWrapColumn",
        "editor.wordWrapColumn": 100
    }
}
```

#### PHPStorm / IntelliJ (Best Option)

PHPStorm has better support for automatic line breaking:

1. **Settings > Editor > Code Style > PHP**
   - Set **Hard wrap at**: `100` characters
   - Enable **Wrap on typing**
   - In **Wrapping and Braces**, enable **Chained method calls** → **Wrap if long**

2. **Settings > Editor > Actions on Save**
   - Enable **Reformat code**
   - Enable **Run PHP CS Fixer** (point to `vendor/bin/pint`)

This will automatically break long method chains as you type!

### Option 2: Manual Breaking + Pint Formatting (Current Approach)

1. **Manually break** long method chains when writing code (press Enter after `->`)
2. Run `vendor/bin/pint` to format them with proper indentation
3. Pint will maintain the formatting going forward

**Example:**
```php
// Before (single line - too long)
Column::make(__('ui.table.users.name'), 'name')->sortable()->searchable()->format(fn ($value, $row) => "<strong>{$value}</strong>")->html(),

// Manually break it:
Column::make(__('ui.table.users.name'), 'name')
->sortable()
->searchable()
->format(fn ($value, $row) => "<strong>{$value}</strong>")
->html(),

// Run Pint - it will format with proper indentation:
Column::make(__('ui.table.users.name'), 'name')
    ->sortable()
    ->searchable()
    ->format(fn ($value, $row) => "<strong>{$value}</strong>")
    ->html(),
```

### Option 3: Pre-commit Hook

Ensure all code is formatted before committing:

```bash
#!/bin/sh
# .git/hooks/pre-commit
vendor/bin/pint --dirty
```

This won't break long lines, but ensures formatting is consistent.

## Best Practice Workflow

**Recommended approach:**

1. **While coding:**
   - Manually break long method chains (press Enter after `->`)
   - Or use PHPStorm's auto-wrap feature
   - Don't worry about perfect indentation - Pint will fix it

2. **Before committing:**
   - Run `vendor/bin/pint` to format everything
   - Pint will apply proper indentation to all broken chains

3. **IDE Setup:**
   - Use PHPStorm if possible (best auto-wrap support)
   - Or configure VS Code to show a ruler at 100 characters as a visual guide

## Summary

- ✅ Pint formats method chains correctly once they're broken
- ❌ Pint doesn't automatically break long single-line chains
- 💡 Solution: Break chains manually or use PHPStorm's auto-wrap feature
- 🎯 Then run Pint to ensure consistent formatting

The current setup is optimal - you just need to break long chains manually (or use an IDE that does it automatically), and Pint will handle the formatting.

