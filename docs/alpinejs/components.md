## Components

### Dropdown

A complete dropdown component example:

```html
<div
    x-data="{
        open: false,
        toggle() {
            if (this.open) {
                return this.close()
            }
            this.$refs.button.focus()
            this.open = true
        },
        close(focusAfter) {
            if (! this.open) return
            this.open = false
            focusAfter && focusAfter.focus()
        }
    }"
    @keydown.escape.prevent.stop="close($refs.button)"
    @focusin.window="! $refs.panel.contains($event.target) && close()"
    x-id="['dropdown-button']"
    class="relative"
>
    <!-- Button -->
    <button
        x-ref="button"
        @click="toggle()"
        :aria-expanded="open"
        :aria-controls="$id('dropdown-button')"
        type="button"
    >
        Options
    </button>

    <!-- Panel -->
    <div
        x-ref="panel"
        x-show="open"
        x-transition.origin.top.left
        @click.outside="close($refs.button)"
        :id="$id('dropdown-button')"
        x-cloak
    >
        <!-- Dropdown items -->
    </div>
</div>
```

[Read more components →](https://alpinejs.dev/components)

---

