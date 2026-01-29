# Chart Component

**Location:** `resources/views/components/ui/chart.blade.php`

**Class:** `app/View/Components/Ui/Chart.php`

**Component Name:** `<x-ui.chart>`

## Description

A powerful, theme-aware wrapper for Chart.js that renders visual charts from backend-generated data. It uses a class-based component to consistently transform contracts/DTOs into the format expected by Chart.js, ensuring type safety and easy integration.

## Props

| Prop | Type | Default | Description |
| :--- | :--- | :--- | :--- |
| `config` | `ChartPayload \| array` | `[]` | The chart configuration. Can be a raw array or a `ChartPayload` DTO from `ChartBuilder`. |
| `height` | `string` | `'300px'` | Valid CSS height string for the chart container. |

## Usage Examples

### Using ChartBuilder (Recommended)

In your Livewire component:

```php
use App\Enums\Stats\ChartType;
use App\Services\Stats\ChartBuilder;

public function getSalesChartProperty()
{
    // Returns a ChartPayload object
    return ChartBuilder::make()
        ->type(ChartType::LINE)
        ->title('Monthly Sales')
        ->labels(['Jan', 'Feb', 'Mar'])
        ->dataset('Revenue', [10, 20, 30])
        ->build();
}
```

In your Blade view:

```blade
<x-ui.chart :config="$this->salesChart" height="400px" />
```

### Using Raw Arrays

You can also pass a raw array matching the Chart.js configuration structure:

```blade
@php
    $rawConfig = [
        'type' => 'bar',
        'data' => [
            'labels' => ['A', 'B'],
            'datasets' => [
                ['label' => 'Test', 'data' => [1, 2]]
            ]
        ]
    ];
@endphp

<x-ui.chart :config="$rawConfig" />
```

## Security & CSP

This component is designed to be **Content Security Policy (CSP)** compliant.
*   **Dynamic Loading**: It uses `import()` in Alpine.js to load Chart.js on demand.
*   **CDN**: It loads from `cdn.jsdelivr.net` (ES Module). Ensure this domain is allowed in your CSP `connect-src` and `script-src`.
*   **No Inline Scripts**: It avoids inline `<script>` tags entirely.
*   **JSON Handling**: It passes configuration via a `data-config` attribute, avoiding `@js()` directive issues inside `x-data`.

## Implementation Details

*   **Class Component**: Uses `App\View\Components\Ui\Chart` to handle data transformation.
*   **Alpine.js**: Uses `resources/js/alpine/chart-ui.js` for client-side rendering.
*   **Initialization**: `x-init="init($wire)"` allows passing the Livewire object for future interactivity (e.g. clicks).
