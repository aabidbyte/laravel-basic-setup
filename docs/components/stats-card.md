# Stat Card Component

**Location:** `resources/views/components/ui/stats/card.blade.php`

**Component Name:** `<x-ui.stats.card>`

## Description

A versatile metric card component designed to display single-value statistics with trends, icons, and theme-aware styling. It is perfect for dashboards and summary views.

## Props

| Prop | Type | Default | Description |
| :--- | :--- | :--- | :--- |
| `stat` | `MetricPayload \| array` | `[]` | The metric data. Can be a raw array or a `MetricPayload` DTO from `MetricBuilder`. |

## Comparison of Variant Styles

The component supports visual variants defined in `StatVariant` enum:
*   `default`: Standard card with background color (shadowed).
*   `outline`: Transparent background with border.
*   `solid`: Full background fill with contrast text.

## Usage Examples

### Using MetricBuilder (Recommended)

In your Livewire component:

```php
use App\Enums\Stats\StatTrend;
use App\Services\Stats\MetricBuilder;

public function getUserCountStatProperty()
{
    return MetricBuilder::make()
        ->label('Total Users')
        ->value(1240)
        ->trend(5, StatTrend::UP)
        ->icon('users')
        ->color('primary')
        ->build();
}
```

In your Blade view:

```blade
<x-ui.stats.card :stat="$this->userCountStat" />
```

### Using Raw Arrays

```blade
<x-ui.stats.card :stat="[
    'label' => 'Revenue',
    'value' => '$50,000',
    'trend' => 'up',
    'trend_value' => 12,
    'color' => 'success',
    'icon' => 'currency-dollar'
]" />
```

## Architecture

This component is a simple **Anonymous Blade Component** that renders HTML based on the input payload. It maps:
*   `trend` (up/down/neutral) to specific icons and colors (green/red/gray).
*   `variant` to specific Tailwind CSS classes.
