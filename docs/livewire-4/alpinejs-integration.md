## AlpineJS Integration

Livewire includes Alpine.js and provides seamless integration.

### x-data

Use `x-data` with Livewire:

```blade
<div x-data="{ open: false }">
    <button @click="open = !open">Toggle</button>
    <div x-show="open">Content</div>
</div>
```

### x-text

Use `x-text` with Livewire properties:

```blade
<div x-text="$wire.title"></div>
```

### x-on

Listen for events:

```blade
<button @click="$wire.save()">Save</button>
```

### $wire Object

The `$wire` object provides access to Livewire from Alpine:

**Properties:**

```javascript
$wire.title = "New Title";
let title = $wire.title;
```

**Methods:**

```javascript
$wire.save();
$wire.delete(123);
```

**Refresh:**

```javascript
$wire.$refresh();
```

**Dispatch:**

```javascript
$wire.$dispatch("event-name", { data: "value" });
```

**On:**

```javascript
$wire.$on("event-name", (data) => {
    console.log(data);
});
```

**El:**

```javascript
let element = $wire.$el;
```

**Get:**

```javascript
let value = $wire.$get("property");
```

**Set:**

```javascript
$wire.$set("property", "value");
```

**Toggle:**

```javascript
$wire.$toggle("property");
```

**Call:**

```javascript
$wire.$call("method", arg1, arg2);
```

**JS:**

```javascript
$wire.$js.methodName = () => {
    // Custom JavaScript method
};
```

**Entangle:**

> ⚠️ **Important**: In Livewire v3/v4, **refrain from using the `@entangle` directive**. While it was recommended in Livewire v2, `$wire.$entangle()` is now preferred as it is a more robust utility and avoids certain issues when removing DOM elements.

Use `$wire.$entangle()` in Alpine.js data components:

```javascript
Alpine.data("component", () => ({
    title: $wire.$entangle("title"),
}));
```

**In Blade templates, use `$wire.$entangle()` instead of `@entangle`:**

```blade
<!-- ❌ Avoid: @entangle directive -->
<div x-data="{ open: @entangle('isOpen').live }">

<!-- ✅ Preferred: $wire.$entangle() -->
<div x-data="{ open: $wire.$entangle('isOpen') }">
```

**Watch:**

```javascript
$wire.$watch("title", (value) => {
    console.log("Title changed:", value);
});
```

**Upload:**

```javascript
$wire.$upload("photo", file, (progress) => {
    console.log("Progress:", progress);
});
```

**Intercept:**

```javascript
$wire.$intercept("save", ({ component, params, preventDefault }) => {
    // Intercept and modify
});
```

### Manual Bundling

If you need to bundle Alpine manually:

```javascript
import Alpine from "alpinejs";
import Livewire from "@livewire/livewire";

Alpine.plugin(Livewire);
Alpine.start();
```

