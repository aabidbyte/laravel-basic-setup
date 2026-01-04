<?php

namespace App\Auth;

use Illuminate\Auth\Passwords\PasswordBrokerManager as BasePasswordBrokerManager;
use Illuminate\Auth\Passwords\TokenRepositoryInterface;

/**
 * Custom password broker manager that uses our custom token repository
 * with UUID support via model events.
 */
class PasswordBrokerManager extends BasePasswordBrokerManager
{
    /**
     * Create a token repository instance based on the given configuration.
     */
    protected function createTokenRepository(array $config): TokenRepositoryInterface
    {
        $key = $this->app['config']['app.key'];

        if (str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr($key, 7), true);
        }

        if (isset($config['driver']) && $config['driver'] === 'cache') {
            return parent::createTokenRepository($config);
        }

        return new DatabaseTokenRepository(
            $this->app['db']->connection($config['connection'] ?? null),
            $this->app['hash'],
            $config['table'],
            $key,
            ($config['expire'] ?? 60) * 60,
            $config['throttle'] ?? 0,
        );
    }
}
