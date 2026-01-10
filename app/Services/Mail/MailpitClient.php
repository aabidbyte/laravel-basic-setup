<?php

declare(strict_types=1);

namespace App\Services\Mail;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Mailpit API Client.
 *
 * Provides methods to interact with Mailpit's HTTP API for testing email functionality.
 * Mailpit API runs on port 8025 by default.
 *
 * @see https://mailpit.axllent.org/docs/api-v1/
 */
class MailpitClient
{
    protected string $baseUrl;

    public function __construct(?string $baseUrl = null)
    {
        $this->baseUrl = $baseUrl ?? env('MAILPIT_API_URL', 'http://localhost:8025');
    }

    /**
     * Get all messages from Mailpit.
     *
     * @param  int  $limit  Maximum number of messages to return
     * @return array<int, array<string, mixed>>
     */
    public function getMessages(int $limit = 50): array
    {
        $response = Http::get("{$this->baseUrl}/api/v1/messages", [
            'limit' => $limit,
        ]);

        if ($response->successful()) {
            return $response->json('messages', []);
        }

        Log::warning('Mailpit: Failed to get messages', ['status' => $response->status()]);

        return [];
    }

    /**
     * Get a single message by ID.
     *
     * @param  string  $id  Message ID
     * @return array<string, mixed>|null
     */
    public function getMessage(string $id): ?array
    {
        $response = Http::get("{$this->baseUrl}/api/v1/message/{$id}");

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    /**
     * Search messages by query.
     *
     * @param  string  $query  Search query (subject, to, from, etc.)
     * @return array<int, array<string, mixed>>
     */
    public function searchMessages(string $query): array
    {
        $response = Http::get("{$this->baseUrl}/api/v1/search", [
            'query' => $query,
        ]);

        if ($response->successful()) {
            return $response->json('messages', []);
        }

        return [];
    }

    /**
     * Delete all messages from Mailpit.
     */
    public function deleteAllMessages(): bool
    {
        $response = Http::delete("{$this->baseUrl}/api/v1/messages");

        return $response->successful();
    }

    /**
     * Get the latest message.
     *
     * @return array<string, mixed>|null
     */
    public function getLatestMessage(): ?array
    {
        $messages = $this->getMessages(1);

        return $messages[0] ?? null;
    }

    /**
     * Get messages sent to a specific email address.
     *
     * @param  string  $email  Email address
     * @return array<int, array<string, mixed>>
     */
    public function getMessagesTo(string $email): array
    {
        return $this->searchMessages("to:{$email}");
    }

    /**
     * Get the full message content including HTML body.
     *
     * @param  string  $id  Message ID
     */
    public function getMessageHtml(string $id): ?string
    {
        $message = $this->getMessage($id);

        return $message['HTML'] ?? null;
    }

    /**
     * Get the plain text body of a message.
     *
     * @param  string  $id  Message ID
     */
    public function getMessageText(string $id): ?string
    {
        $message = $this->getMessage($id);

        return $message['Text'] ?? null;
    }

    /**
     * Check if Mailpit is running and accessible.
     */
    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(2)->get("{$this->baseUrl}/api/v1/messages", ['limit' => 1]);

            return $response->successful();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Wait for a message to arrive (with timeout).
     *
     * @param  string  $toEmail  Expected recipient email
     * @param  int  $timeoutSeconds  Maximum time to wait
     * @param  int  $pollIntervalMs  Polling interval in milliseconds
     * @return array<string, mixed>|null
     */
    public function waitForMessage(string $toEmail, int $timeoutSeconds = 10, int $pollIntervalMs = 500): ?array
    {
        $startTime = time();

        while ((time() - $startTime) < $timeoutSeconds) {
            $messages = $this->getMessagesTo($toEmail);

            if (! empty($messages)) {
                return $messages[0];
            }

            usleep($pollIntervalMs * 1000);
        }

        return null;
    }

    /**
     * Extract links from message HTML.
     *
     * @param  string  $messageId  Message ID
     * @return array<string>
     */
    public function extractLinksFromMessage(string $messageId): array
    {
        $html = $this->getMessageHtml($messageId);
        if ($html === null) {
            return [];
        }

        preg_match_all('/href=["\']([^"\']+)["\']/', $html, $matches);

        return $matches[1] ?? [];
    }
}
