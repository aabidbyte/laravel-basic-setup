# wire:stream

`wire:stream` allows you to stream content from the server into your component in real-time, which is useful for long-running tasks or AI-generated responses (e.g., GPT-style streaming).

## Basic Usage

```blade
<div wire:stream="chat-response">
    <!-- Streamed content appears here -->
</div>
```

## Backend Usage

```php
public function generate()
{
    $this->stream('chat-response', 'Hello... ', replace: false);
    sleep(1);
    $this->stream('chat-response', 'World!');
}
```

-   `replace: true`: Replaces the existing content of the stream target.
-   `replace: false`: (Default) Appends the new content.
