## JavaScript in View-Based Components

View-based components can now include `<script>` tags without the `@script` wrapper. These scripts are served as separate cached files for better performance and automatic `$wire` binding:

```blade
<div>
    <!-- Your component template -->
</div>

<script>
    // $wire is automatically bound as 'this'
    this.count++  // Same as $wire.count++

    // $wire is still available if preferred
    $wire.save()
</script>
```

