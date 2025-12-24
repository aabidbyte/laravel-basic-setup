## Upgrade Guide (V2 to V3)

Key breaking changes in Alpine V3:

1. **`$el` is now always the current element** - Use `$root` for the root element
2. **Automatically evaluate `init()` functions** - No need to manually call `init()` on data objects
3. **Need to call `Alpine.start()` after import** - Required when importing as a module
4. **`x-show.transition` is now `x-transition`** - Use `x-show="open" x-transition` instead
5. **`x-if` no longer supports `x-transition`** - Use `x-show` with transitions instead
6. **`x-data` cascading scope** - Scope is available to all children unless overwritten
7. **`x-init` no longer accepts a callback return** - Changes to initialization behavior
8. **Returning `false` from event handlers** - No longer implicitly prevents default
9. **`x-spread` is now `x-bind`** - Directive renamed
10. **`x-ref` no longer supports binding** - Only static references supported
11. **Use global lifecycle events** - Instead of `Alpine.deferLoadingAlpine()`
12. **IE11 no longer supported**

[Read full upgrade guide →](https://alpinejs.dev/upgrade-guide)

---

