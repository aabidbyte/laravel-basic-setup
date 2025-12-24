## File Uploads

Handle file uploads in Livewire components.

### WithFileUploads Trait

Use the trait:

```php
use Livewire\WithFileUploads;
use Livewire\Component;

class UploadPhoto extends Component
{
    use WithFileUploads;

    public $photo;
}
```

### wire:model on File Inputs

Bind to file input:

```blade
<input type="file" wire:model="photo">
```

### Storing

Store uploaded files:

```php
public function save()
{
    $this->validate([
        'photo' => 'image|max:1024',
    ]);

    $path = $this->photo->store('photos');
}
```

### Multiple Files

Upload multiple files:

```php
public $photos = [];
```

```blade
<input type="file" wire:model="photos" multiple>
```

### Validation

Validate files:

```php
$this->validate([
    'photo' => 'required|image|max:1024|mimes:jpeg,png',
]);
```

### Temporary Preview URLs

Preview before storing:

```blade
@if ($photo)
    <img src="{{ $photo->temporaryUrl() }}">
@endif
```

### Testing

Test file uploads:

```php
Livewire::test(UploadPhoto::class)
    ->set('photo', UploadedFile::fake()->image('photo.jpg'))
    ->call('save')
    ->assertHasNoErrors();
```

### S3 Direct Upload

Upload directly to S3:

```php
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

$path = $this->photo->storeAs('photos', 'photo.jpg', 's3');
```

### Loading/Progress Indicators

Show progress:

```blade
<div wire:loading wire:target="photo">Uploading...</div>
<input type="file" wire:model="photo">
```

### Cancelling

Cancel upload:

```php
$this->photo->delete();
```

### JavaScript API

Upload from JavaScript:

```javascript
$wire.$upload("photo", file, (progress) => {
    console.log("Progress:", progress);
});
```

### Configuration

Configure in `config/livewire.php`:

```php
'file_uploads' => [
    'disk' => 'local',
    'directory' => 'livewire-tmp',
],
```

