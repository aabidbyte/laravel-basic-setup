## File Downloads

Handle file downloads in Livewire.

### Standard Laravel Responses

Return download response:

```php
public function download()
{
    return Storage::download('file.pdf');
}
```

### Streaming

Stream large files:

```php
public function download()
{
    return Storage::response('large-file.zip');
}
```

### Testing

Test downloads:

```php
Livewire::test(DownloadFile::class)
    ->call('download')
    ->assertFileDownloaded('file.pdf');
```

