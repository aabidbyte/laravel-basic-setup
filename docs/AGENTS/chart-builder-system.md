# Stats & Chart Builder System

The Stats & Chart Builder System provides a fluent PHP API for creating visual charts (Chart.js) and metric cards. It decouples the backend logic from the frontend implementation, allowing for a standardized, theme-aware, and type-safe way to display statistics.

## Architecture

### Backend
- **Base Component**: `BaseChartsComponent` (extends `LivewireBaseComponent`)
- **Builders**: `ChartBuilder` and `MetricBuilder` (Fluent Interfaces)
- **DTOs**: `ChartPayload` and `MetricPayload` (Normalized Data)
- **Transformers**: `ChartJsTransformer` (Prepares data for Chart.js)
- **Enums**: `ChartType`, `StatTrend`, `StatVariant`, `PlaceholderType`

### Frontend
- **Blade Components**: `<x-ui.chart>` and `<x-ui.stats.card>`
- **Asset Strategy**: Dynamic import of Chart.js via Alpine.js (avoids Global/Livewire asset injection issues)
- **Alpine**: `chartUi` component (handles async loading and rendering)
- **Theme**: Automatic integration with existing project theme colors

## Implementation Notes

### Class Components
Frontend components like `<x-ui.chart>` should be implemented as **Class Components** (`App\View\Components\Ui\Chart`) when they require data transformation logic. This keeps the Blade view clean and allows for strictly typed preprocessing.

### CSP & Alpine.js
**Critical**: Do NOT use `@js($data)` directive directly inside `x-data` attributes like `x-data="comp(@js($data))"`.
- This often generates `JSON.parse(...)` which can fail with "Undefined variable: JSON" errors in some CSP environments or specific Alpine evaluation contexts.
- **Solution**: Pass complex data via `data-*` attributes (e.g., `data-config="{{ json_encode($data) }}"`) and parse it inside the Alpine component's `init()` method using `JSON.parse(this.$el.dataset.config)`.

## Usage Guide

### 1. Creating Charts

Use the `ChartBuilder` to create chart configurations.

```php
use App\Enums\Stats\ChartType;
use App\Services\Stats\ChartBuilder;

// Inside a Livewire Component
public function getMonthlySalesChartProperty()
{
    return ChartBuilder::make()
        ->type(ChartType::LINE)
        ->title('Monthly Revenue')
        ->labels(['Jan', 'Feb', 'Mar', 'Apr'])
        ->dataset('Revenue', [1200, 1900, 3000, 5000], [
            'borderColor' => 'rgb(75, 192, 192)',
            'tension' => 0.1
        ])
        ->build();
}
```

Render in Blade:

```blade
<x-ui.chart :config="$this->monthlySalesChart" />
```

### 2. Creating Metric Cards

Use the `MetricBuilder` for single-value stats with trends.

```php
use App\Enums\Stats\StatTrend;
use App\Enums\Stats\StatVariant;
use App\Services\Stats\MetricBuilder;

public function getTotalUsersStatProperty()
{
    return MetricBuilder::make()
        ->label('Total Users')
        ->value(1250)
        ->trend(12.5, StatTrend::UP)
        ->icon('users')
        ->color('primary')
        ->variant(StatVariant::DEFAULT)
        ->build();
}
```

Render in Blade:

```blade
<x-ui.stats.card :stat="$this->totalUsersStat" />
```

### 3. Rendering via Custom Components

If you need a specific layout, you can iterate over the payloads and render them manually using dynamic components or slots.

```blade
<div class="grid grid-cols-3 gap-4">
    @foreach($stats as $stat)
        <x-dynamic-component :component="$stat->view" :data="$stat->data" />
    @endforeach
</div>
```

## Advanced Features

### 1. Date Range Filtering
`BaseChartsComponent` provides built-in date range support with URL persistence.

*   **Properties**: `$dateFrom`, `$dateTo` (synced via `#[Url]`)
*   **Listeners**:
    *   `date-range-changed`: Updates ALL chart components.
    *   `date-range-changed:{ClassName}`: Updates ONLY the specific component class.
*   **Usage**: Access `$this->dateFrom` inside your chart property methods.

```php
public function getSalesChartProperty()
{
    $query = Order::query();
    if ($this->dateFrom) {
        $query->where('created_at', '>=', $this->dateFrom);
    }
    // ...
}
```

### 2. Custom Layout Schema
Override the `schema()` method in your component to define precise grid layouts.

```php
protected function schema(): ?array
{
    return [
        'totalUsersStat' => ['class' => 'col-span-1 md:col-span-2'], // Half width
        'activeUsersStat' => ['class' => 'col-span-1 md:col-span-2'],
        'registrationsChart' => ['class' => 'col-span-full'], // Full width
    ];
}
```

### 3. Placeholders
Charts use `PlaceholderType` Enum for skeletons. The default is `CHARTS_STATS` (mixed grid).
Override `$placeholderType` in your component to change this.

```php
use App\Enums\Ui\PlaceholderType;

protected PlaceholderType $placeholderType = PlaceholderType::CHARTS;
```

## Theme Integration

*   **Colors**: The system uses Tailwind CSS classes for metric cards (`text-primary`, `bg-base-100`).
*   **Charts**: Charts render into a canvas. Future improvements will map theme colors (CSS variables) directly to chart datasets automatically.

## AI Agent Instructions

When asking an AI agent to create stats:
1.  Specify the **Type** (Chart or Metric).
2.  Provide the **Data Source** (Model, Query).
3.  The Agent should use `ChartBuilder` or `MetricBuilder`.
4.  The Agent should **NOT** write raw HTML or JS for charts.
5.  All labels must be translatable keys (the builders handle `__()` automatically).

## Mistakes to Avoid

*   ❌ **Do NOT** inline Chart.js code in Blade templates. Use `<x-ui.chart>`.
*   ❌ **Do NOT** return raw arrays from Livewire properties. Use `->build()` which returns type-safe DTOs.
*   ❌ **Do NOT** hardcode colors if possible. Use theme semantic names (`primary`, `success`, `error`).
*   ❌ **Do NOT** use `@js()` in `x-data`. Use `data-config` attribute instead.
