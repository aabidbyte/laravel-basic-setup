<?php

namespace Illuminate\Contracts\Auth;

interface Guard
{
    /**
     * Get the currently authenticated user.
     *
     * @return \App\Models\User|null
     */
    public function user();

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|string|null
     */
    public function id();

    /**
     * Check if the user is authenticated.
     */
    public function check(): bool;

    /**
     * Check if the user is a guest.
     */
    public function guest(): bool;
}

interface StatefulGuard
{
    /**
     * Get the currently authenticated user.
     *
     * @return \App\Models\User|null
     */
    public function user();

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|string|null
     */
    public function id();

    /**
     * Log the user out of the application.
     */
    public function logout(): void;

    /**
     * Attempt to authenticate a user using the given credentials.
     *
     * @param  bool  $remember
     */
    public function attempt(array $credentials = [], $remember = false): bool;

    /**
     * Log a user into the application.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  bool  $remember
     */
    public function login($user, $remember = false): void;
}

interface Factory
{
    /**
     * Get the currently authenticated user.
     *
     * @return \App\Models\User|null
     */
    public function user();

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|string|null
     */
    public function id();
}

namespace Spatie\Permission\Traits;

interface HasRoles
{
    /**
     * Check if the user has a specific role.
     *
     * @param  string|array  $roles
     * @param  string|null  $guard
     */
    public function hasRole($roles, $guard = null): bool;

    /**
     * Check if the user has any of the given roles.
     *
     * @param  string|array  $roles
     * @param  string|null  $guard
     */
    public function hasAnyRole($roles, $guard = null): bool;

    /**
     * Check if the user has all of the given roles.
     *
     * @param  string|array  $roles
     * @param  string|null  $guard
     */
    public function hasAllRoles($roles, $guard = null): bool;

    /**
     * Check if the user has a specific permission.
     *
     * @param  string|array  $permissions
     * @param  string|null  $guard
     */
    public function hasPermissionTo($permissions, $guard = null): bool;

    /**
     * Check if the user has any of the given permissions.
     *
     * @param  string|array  $permissions
     * @param  string|null  $guard
     */
    public function hasAnyPermission($permissions, $guard = null): bool;

    /**
     * Check if the user has all of the given permissions.
     *
     * @param  string|array  $permissions
     * @param  string|null  $guard
     */
    public function hasAllPermissions($permissions, $guard = null): bool;

    /**
     * Check if the user can perform an action.
     *
     * @param  string|array  $permissions
     * @param  string|null  $guard
     */
    public function can($permissions, $guard = null): bool;

    /**
     * Check if the user cannot perform an action.
     *
     * @param  string|array  $permissions
     * @param  string|null  $guard
     */
    public function cannot($permissions, $guard = null): bool;

    /**
     * Get all roles for the user.
     *
     * @param  string|null  $guard
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRoles($guard = null);

    /**
     * Get all permissions for the user.
     *
     * @param  string|null  $guard
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPermissions($guard = null);

    /**
     * Assign a role to the user.
     *
     * @param  string|array|\Spatie\Permission\Models\Role  $roles
     * @param  string|null  $guard
     * @return $this
     */
    public function assignRole($roles, $guard = null): static;

    /**
     * Remove a role from the user.
     *
     * @param  string|array|\Spatie\Permission\Models\Role  $roles
     * @param  string|null  $guard
     * @return $this
     */
    public function removeRole($roles, $guard = null): static;

    /**
     * Sync roles for the user.
     *
     * @param  string|array|\Spatie\Permission\Models\Role  $roles
     * @param  string|null  $guard
     * @return $this
     */
    public function syncRoles($roles, $guard = null): static;

    /**
     * Give permission to the user.
     *
     * @param  string|array|\Spatie\Permission\Models\Permission  $permissions
     * @param  string|null  $guard
     * @return $this
     */
    public function givePermissionTo($permissions, $guard = null): static;

    /**
     * Revoke permission from the user.
     *
     * @param  string|array|\Spatie\Permission\Models\Permission  $permissions
     * @param  string|null  $guard
     * @return $this
     */
    public function revokePermissionTo($permissions, $guard = null): static;

    /**
     * Sync permissions for the user.
     *
     * @param  string|array|\Spatie\Permission\Models\Permission  $permissions
     * @param  string|null  $guard
     * @return $this
     */
    public function syncPermissions($permissions, $guard = null): static;
}

namespace Illuminate\Support\Facades;

interface Log
{
    /**
     * Log an emergency message to the logs.
     *
     * @param  string  $message
     */
    public static function emergency($message, array $context = []): void;

    /**
     * Log an alert message to the logs.
     *
     * @param  string  $message
     */
    public static function alert($message, array $context = []): void;

    /**
     * Log a critical message to the logs.
     *
     * @param  string  $message
     */
    public static function critical($message, array $context = []): void;

    /**
     * Log an error message to the logs.
     *
     * @param  string  $message
     */
    public static function error($message, array $context = []): void;

    /**
     * Log a warning message to the logs.
     *
     * @param  string  $message
     */
    public static function warning($message, array $context = []): void;

    /**
     * Log a notice message to the logs.
     *
     * @param  string  $message
     */
    public static function notice($message, array $context = []): void;

    /**
     * Log an informational message to the logs.
     *
     * @param  string  $message
     */
    public static function info($message, array $context = []): void;

    /**
     * Log a debug message to the logs.
     *
     * @param  string  $message
     */
    public static function debug($message, array $context = []): void;

    /**
     * Log a message to the logs.
     *
     * @param  string  $level
     * @param  string  $message
     */
    public static function log($level, $message, array $context = []): void;

    /**
     * Get a log channel instance.
     *
     * @param  string|null  $channel
     * @return \Psr\Log\LoggerInterface
     */
    public static function channel($channel = null);
}

interface Cache
{
    /**
     * Get an item from the cache.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public static function get($key, $default = null);

    /**
     * Store an item in the cache.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @param  int|null  $ttl
     */
    public static function put($key, $value, $ttl = null): bool;

    /**
     * Store an item in the cache if the key doesn't exist.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @param  int|null  $ttl
     */
    public static function add($key, $value, $ttl = null): bool;

    /**
     * Remove an item from the cache.
     *
     * @param  string  $key
     */
    public static function forget($key): bool;

    /**
     * Clear all items from the cache.
     */
    public static function flush(): bool;

    /**
     * Get an item from the cache or store the default value.
     *
     * @param  string  $key
     * @param  callable  $callback
     * @param  int|null  $ttl
     * @return mixed
     */
    public static function remember($key, $callback, $ttl = null);
}

interface Config
{
    /**
     * Get a configuration value.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public static function get($key, $default = null);

    /**
     * Set a configuration value.
     *
     * @param  string  $key
     * @param  mixed  $value
     */
    public static function set($key, $value): void;
}

interface DB
{
    /**
     * Begin a fluent query against a database table.
     *
     * @param  string  $table
     * @return \Illuminate\Database\Query\Builder
     */
    public static function table($table);

    /**
     * Execute a raw SQL query.
     *
     * @param  string  $query
     * @param  array  $bindings
     * @return mixed
     */
    public static function select($query, $bindings = []);

    /**
     * Execute a raw SQL query and return the first result.
     *
     * @param  string  $query
     * @param  array  $bindings
     * @return mixed
     */
    public static function selectOne($query, $bindings = []);

    /**
     * Execute a raw SQL query and return the number of affected rows.
     *
     * @param  string  $query
     * @param  array  $bindings
     */
    public static function statement($query, $bindings = []): int;

    /**
     * Execute a raw SQL query and return the number of affected rows.
     *
     * @param  string  $query
     * @param  array  $bindings
     */
    public static function affectingStatement($query, $bindings = []): int;

    /**
     * Begin a database transaction.
     */
    public static function beginTransaction(): void;

    /**
     * Commit a database transaction.
     */
    public static function commit(): void;

    /**
     * Rollback a database transaction.
     */
    public static function rollback(): void;

    /**
     * Get the database connection.
     *
     * @param  string|null  $name
     * @return \Illuminate\Database\Connection
     */
    public static function connection($name = null);
}

interface Auth
{
    /**
     * Get the currently authenticated user.
     *
     * @return \App\Models\User|null
     */
    public static function user();

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|string|null
     */
    public static function id();

    /**
     * Check if the user is authenticated.
     */
    public static function check(): bool;

    /**
     * Check if the user is a guest.
     */
    public static function guest(): bool;

    /**
     * Get the guard instance.
     *
     * @param  string|null  $guard
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    public static function guard($guard = null);

    /**
     * Log the user out of the application.
     */
    public static function logout(): void;

    /**
     * Attempt to authenticate a user using the given credentials.
     *
     * @param  bool  $remember
     */
    public static function attempt(array $credentials = [], $remember = false): bool;

    /**
     * Log a user into the application.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  bool  $remember
     */
    public static function login($user, $remember = false): void;
}

interface Session
{
    /**
     * Get a value from the session.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public static function get($key, $default = null);

    /**
     * Put a value in the session.
     *
     * @param  string  $key
     * @param  mixed  $value
     */
    public static function put($key, $value): void;

    /**
     * Remove a value from the session.
     *
     * @param  string  $key
     */
    public static function forget($key): void;

    /**
     * Remove all items from the session.
     */
    public static function flush(): void;

    /**
     * Regenerate the session ID.
     *
     * @param  bool  $destroy
     */
    public static function regenerate($destroy = false): bool;

    /**
     * Regenerate the CSRF token value.
     */
    public static function regenerateToken(): void;

    /**
     * Invalidate the current session.
     */
    public static function invalidate(): bool;

    /**
     * Check if a key exists in the session.
     *
     * @param  string  $key
     */
    public static function has($key): bool;
}

interface Route
{
    /**
     * Get the current route name.
     *
     * @return string|null
     */
    public static function currentRouteName();

    /**
     * Get the current route action.
     *
     * @return string|null
     */
    public static function currentRouteAction();

    /**
     * Generate a URL to a named route.
     *
     * @param  string  $name
     * @param  array  $parameters
     * @param  bool  $absolute
     */
    public static function route($name, $parameters = [], $absolute = true): string;

    /**
     * Check if the current route matches a given pattern.
     *
     * @param  string  $pattern
     */
    public static function is($pattern): bool;
}

interface View
{
    /**
     * Get the evaluated view contents for the given view.
     *
     * @param  string  $view
     * @param  array  $data
     * @param  array  $mergeData
     * @return \Illuminate\View\View
     */
    public static function make($view, $data = [], $mergeData = []);

    /**
     * Get the evaluated view contents for the given view.
     *
     * @param  string  $view
     * @param  array  $data
     * @param  array  $mergeData
     */
    public static function render($view, $data = [], $mergeData = []): string;
}

interface Mail
{
    /**
     * Send a new message using a view.
     *
     * @param  string|array  $view
     * @param  array  $data
     * @param  \Closure|string  $callback
     * @return mixed
     */
    public static function send($view, $data = [], $callback = null);

    /**
     * Send a new message using a view.
     *
     * @param  string|array  $view
     * @param  array  $data
     * @param  \Closure|string  $callback
     * @return mixed
     */
    public static function queue($view, $data = [], $callback = null);

    /**
     * Send a new message using a view.
     *
     * @param  string|array  $view
     * @param  array  $data
     * @param  \Closure|string  $callback
     * @return mixed
     */
    public static function later($delay, $view, $data = [], $callback = null);
}

interface Storage
{
    /**
     * Get a filesystem instance.
     *
     * @param  string|null  $disk
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public static function disk($disk = null);

    /**
     * Store the uploaded file on the disk.
     *
     * @param  string  $path
     * @param  \Illuminate\Http\File|\Illuminate\Http\UploadedFile|string  $file
     * @param  array  $options
     * @return string|false
     */
    public static function putFile($path, $file, $options = []);

    /**
     * Store the uploaded file on the disk with a given name.
     *
     * @param  string  $path
     * @param  \Illuminate\Http\File|\Illuminate\Http\UploadedFile|string  $file
     * @param  string  $name
     * @param  array  $options
     * @return string|false
     */
    public static function putFileAs($path, $file, $name, $options = []);
}

interface Schema
{
    /**
     * Determine if the given table exists.
     *
     * @param  string  $table
     */
    public static function hasTable($table): bool;

    /**
     * Determine if the given table has a given column.
     *
     * @param  string  $table
     * @param  string  $column
     */
    public static function hasColumn($table, $column): bool;

    /**
     * Determine if the given table has given columns.
     *
     * @param  string  $table
     * @param  array  $columns
     */
    public static function hasColumns($table, $columns): bool;

    /**
     * Create a new table on the schema.
     *
     * @param  string  $table
     * @param  \Closure  $callback
     */
    public static function create($table, $callback): void;

    /**
     * Drop a table from the schema.
     *
     * @param  string  $table
     */
    public static function drop($table): void;

    /**
     * Drop a table from the schema if it exists.
     *
     * @param  string  $table
     */
    public static function dropIfExists($table): void;
}

namespace Tests;

interface TestCase
{
    /**
     * Set the currently logged in user for the application.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string|null  $driver
     * @return $this
     */
    public function actingAs($user, $driver = null): static;
}

namespace Illuminate\Database\Eloquent\Concerns;

interface HasFactory
{
    /**
     * Get a new factory instance for the model.
     *
     * @param  callable|int|null  $count
     * @param  callable|array  $state
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public static function factory($count = null, $state = []);
}

namespace Illuminate\Database\Eloquent\Factories;

interface Factory
{
    /**
     * Create a new model instance.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $attributes = []);

    /**
     * Create a new model instance without persisting it.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function make(array $attributes = []);

    /**
     * Create a collection of models.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function createMany(int $count, array $attributes = []);

    /**
     * Create a collection of models without persisting them.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function makeMany(int $count, array $attributes = []);
}

namespace Illuminate\Database\Eloquent;

/**
 * Eloquent Builder interface for Intelephense
 *
 * Note: When using macros, the closure's $this refers to a Builder instance.
 * All macro-registered methods (like search, advancedSearch, modalSearch) are available on Builder instances.
 *
 * @method static void macro(string $name, callable $macro) Register a macro method
 * @method static bool hasMacro(string $name) Check if a macro is registered
 */
interface Builder
{
    /**
     * Add a basic where clause to the query.
     *
     * @param  string|array|\Closure  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * @param  string  $boolean
     * @return $this
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and'): static;

    /**
     * Add an "or where" clause to the query.
     *
     * @param  string|array|\Closure  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return $this
     */
    public function orWhere($column, $operator = null, $value = null): static;

    /**
     * Add a "where in" clause to the query.
     *
     * @param  string  $column
     * @param  mixed  $values
     * @param  string  $boolean
     * @param  bool  $not
     * @return $this
     */
    public function whereIn($column, $values, $boolean = 'and', $not = false): static;

    /**
     * Add a "where not in" clause to the query.
     *
     * @param  string  $column
     * @param  mixed  $values
     * @param  string  $boolean
     * @return $this
     */
    public function whereNotIn($column, $values, $boolean = 'and'): static;

    /**
     * Add a "where null" clause to the query.
     *
     * @param  string  $column
     * @param  string  $boolean
     * @param  bool  $not
     * @return $this
     */
    public function whereNull($column, $boolean = 'and', $not = false): static;

    /**
     * Add a "where not null" clause to the query.
     *
     * @param  string  $column
     * @param  string  $boolean
     * @return $this
     */
    public function whereNotNull($column, $boolean = 'and'): static;

    /**
     * Add an "order by" clause to the query.
     *
     * @param  string  $column
     * @param  string  $direction
     * @return $this
     */
    public function orderBy($column, $direction = 'asc'): static;

    /**
     * Add a "group by" clause to the query.
     *
     * @param  array|string  $groups
     * @return $this
     */
    public function groupBy($groups): static;

    /**
     * Add a "having" clause to the query.
     *
     * @param  string  $column
     * @param  string  $operator
     * @param  mixed  $value
     * @param  string  $boolean
     * @return $this
     */
    public function having($column, $operator = null, $value = null, $boolean = 'and'): static;

    /**
     * Set the limit and offset for a given page.
     *
     * @param  int  $page
     * @param  int  $perPage
     * @return $this
     */
    public function forPage($page, $perPage = 15): static;

    /**
     * Add a relationship count / exists condition to the query.
     *
     * @param  string  $relation
     * @param  string  $operator
     * @param  int  $count
     * @param  string  $boolean
     * @param  \Closure|null  $callback
     * @return $this
     */
    public function has($relation, $operator = '>=', $count = 1, $boolean = 'and', $callback = null): static;

    /**
     * Add a relationship count / exists condition to the query with an "or".
     *
     * @param  string  $relation
     * @param  string  $operator
     * @param  int  $count
     * @return $this
     */
    public function orHas($relation, $operator = '>=', $count = 1): static;

    /**
     * Add a relationship count / exists condition to the query.
     *
     * @param  string  $relation
     * @param  string  $operator
     * @param  int  $count
     * @param  string  $boolean
     * @param  \Closure|null  $callback
     * @return $this
     */
    public function doesntHave($relation, $boolean = 'and', $callback = null): static;

    /**
     * Add a relationship count / exists condition to the query with an "or".
     *
     * @param  string  $relation
     * @param  string  $operator
     * @param  int  $count
     * @return $this
     */
    public function orDoesntHave($relation): static;

    /**
     * Add a relationship count / exists condition to the query with where clauses.
     *
     * @param  string  $relation
     * @param  \Closure  $callback
     * @param  string  $operator
     * @param  int  $count
     * @return $this
     */
    public function whereHas($relation, $callback = null, $operator = '>=', $count = 1): static;

    /**
     * Add a relationship count / exists condition to the query with where clauses and an "or".
     *
     * @param  string  $relation
     * @param  \Closure  $callback
     * @param  string  $operator
     * @param  int  $count
     * @return $this
     */
    public function orWhereHas($relation, $callback = null, $operator = '>=', $count = 1): static;

    /**
     * Add a relationship count / exists condition to the query with where clauses.
     *
     * @param  string  $relation
     * @param  \Closure  $callback
     * @return $this
     */
    public function whereDoesntHave($relation, $callback = null): static;

    /**
     * Add a relationship count / exists condition to the query with where clauses and an "or".
     *
     * @param  string  $relation
     * @param  \Closure  $callback
     * @return $this
     */
    public function orWhereDoesntHave($relation, $callback = null): static;

    /**
     * Add a relationship count / exists condition to the query with where clauses.
     *
     * @param  string  $relation
     * @param  \Closure  $callback
     * @return $this
     */
    public function withCount($relations): static;

    /**
     * Eager load relationships on the model.
     *
     * @param  array|string  $relations
     * @return $this
     */
    public function with($relations): static;

    /**
     * Eager load relationships on the model.
     *
     * @param  array|string  $relations
     * @return $this
     */
    public function without($relations): static;

    /**
     * Eager load relationships on the model.
     *
     * @param  array|string  $relations
     * @return $this
     */
    public function load($relations);

    /**
     * Eager load relationships on the model.
     *
     * @param  array|string  $relations
     * @return $this
     */
    public function loadMissing($relations);

    /**
     * Get the first result from the query.
     *
     * @param  array|string  $columns
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function first($columns = ['*']);

    /**
     * Get the first result from the query or throw an exception.
     *
     * @param  array|string  $columns
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function firstOrFail($columns = ['*']);

    /**
     * Get the first result from the query or create a new instance.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function firstOrCreate($attributes, $values = []);

    /**
     * Get the first result from the query or create a new instance.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function firstOrNew($attributes, $values = []);

    /**
     * Get the first result from the query or create a new instance.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function updateOrCreate($attributes, $values = []);

    /**
     * Get the first result from the query or create a new instance.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function upsert($values, $uniqueBy, $update = null): int;

    /**
     * Get the first result from the query or create a new instance.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function find($id, $columns = ['*']);

    /**
     * Get the first result from the query or create a new instance.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function findOrFail($id, $columns = ['*']);

    /**
     * Get the first result from the query or create a new instance.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function findOrNew($id, $columns = ['*']);

    /**
     * Get the first result from the query or create a new instance.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function findMany($ids, $columns = ['*']);

    /**
     * Get the results from the query.
     *
     * @param  array|string  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function get($columns = ['*']);

    /**
     * Get the first result from the query or create a new instance.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = 15, $columns = ['*'], $pageName = 'page', $page = null);

    /**
     * Get the first result from the query or create a new instance.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    public function simplePaginate($perPage = 15, $columns = ['*'], $pageName = 'page', $page = null);

    /**
     * Get the count of results from the query.
     *
     * @param  string  $columns
     */
    public function count($columns = '*'): int;

    /**
     * Get the sum of the given column's values.
     *
     * @param  string  $column
     * @return int|float
     */
    public function sum($column);

    /**
     * Get the average of the given column's values.
     *
     * @param  string  $column
     * @return int|float
     */
    public function avg($column);

    /**
     * Get the minimum value of the given column.
     *
     * @param  string  $column
     * @return mixed
     */
    public function min($column);

    /**
     * Get the maximum value of the given column.
     *
     * @param  string  $column
     * @return mixed
     */
    public function max($column);

    /**
     * Get the first result from the query or create a new instance.
     *
     * @param  array  $attributes
     * @param  array  $values
     */
    public function exists(): bool;

    /**
     * Get the first result from the query or create a new instance.
     *
     * @param  array  $attributes
     * @param  array  $values
     */
    public function doesntExist(): bool;

    /**
     * Get the first result from the query or create a new instance.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return $this
     */
    public function latest($column = 'created_at'): static;

    /**
     * Get the first result from the query or create a new instance.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return $this
     */
    public function oldest($column = 'created_at'): static;

    /**
     * Get the first result from the query or create a new instance.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return $this
     */
    public function inRandomOrder($seed = ''): static;

    /**
     * Get the first result from the query or create a new instance.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return $this
     */
    public function limit($value): static;

    /**
     * Get the first result from the query or create a new instance.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return $this
     */
    public function offset($value): static;

    /**
     * Get the first result from the query or create a new instance.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return $this
     */
    public function take($value): static;

    /**
     * Get the first result from the query or create a new instance.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return $this
     */
    public function skip($value): static;

    /**
     * Get the first result from the query or create a new instance.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return $this
     */
    public function select($columns = ['*']): static;

    /**
     * Get the first result from the query or create a new instance.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return $this
     */
    public function selectRaw($expression, $bindings = []): static;

    /**
     * Get the first result from the query or create a new instance.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return $this
     */
    public function addSelect($column): static;

    /**
     * Get the first result from the query or create a new instance.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return $this
     */
    public function distinct(): static;

    /**
     * Get the first result from the query or create a new instance.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return $this
     */
    public function from($table): static;

    /**
     * Get the first result from the query or create a new instance.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return $this
     */
    public function join($table, $first, $operator = null, $second = null, $type = 'inner', $where = false): static;

    /**
     * Get the first result from the query or create a new instance.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return $this
     */
    public function leftJoin($table, $first, $operator = null, $second = null): static;

    /**
     * Get the first result from the query or create a new instance.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return $this
     */
    public function rightJoin($table, $first, $operator = null, $second = null): static;

    /**
     * Get the first result from the query or create a new instance.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return $this
     */
    public function crossJoin($table, $first = null, $operator = null, $second = null): static;

    /**
     * Get the first result from the query or create a new instance.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return $this
     */
    public function union($query, $all = false): static;

    /**
     * Get the first result from the query or create a new instance.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return $this
     */
    public function unionAll($query): static;

    /**
     * Get the first result from the query or create a new instance.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return $this
     */
    public function when($value, $callback, $default = null): static;

    /**
     * Get the first result from the query or create a new instance.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return $this
     */
    public function unless($value, $callback, $default = null): static;

    /**
     * Get the first result from the query or create a new instance.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return $this
     */
    public function tap($callback): static;

    /**
     * Get the first result from the query or create a new instance.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function macro($name, $macro);

    /**
     * Get the first result from the query or create a new instance.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function mixin($mixin, $replace = true);

    /**
     * Get the first result from the query or create a new instance.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function hasMacro($name);

    /**
     * Get the first result from the query or create a new instance.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getMacro($name);

    /**
     * Get the first result from the query or create a new instance.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function __call($method, $parameters);

    /**
     * Simple search macro for single/multiple columns.
     *
     * @return $this
     */
    public function search(string $query, array|string $columns = []): static;

    /**
     * Advanced search macro with multiple terms and exact matches.
     *
     * @return $this
     */
    public function advancedSearch(string $query, array|string $columns = []): static;

    /**
     * Modal search macro for consistent modal search functionality.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function modalSearch(string $query = '', array|string $columns = [], int $perPage = 10);
}

namespace Inertia;

/**
 * Inertia response class.
 *
 * This class is provided by the inertiajs/inertia-laravel package.
 * It's defined here for Intelephense support when the package is not yet installed.
 */
class Response
{
    // Stub class for Intelephense
}

/**
 * Inertia middleware base class.
 *
 * This class is provided by the inertiajs/inertia-laravel package.
 * It's defined here for Intelephense support when the package is not yet installed.
 */
abstract class Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(\Illuminate\Http\Request $request): ?string
    {
        return null;
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(\Illuminate\Http\Request $request): array
    {
        return [];
    }
}

/**
 * Inertia facade for rendering Inertia pages.
 *
 * This class is provided by the inertiajs/inertia-laravel package.
 * It's defined here for Intelephense support when the package is not yet installed.
 */
class Inertia
{
    /**
     * Render an Inertia page.
     */
    public static function render(string $component, array $props = []): Response
    {
        // Stub implementation for Intelephense
        return new Response;
    }
}

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

/**
 * Handle Inertia requests middleware.
 *
 * This class is created when installing React or Vue stack.
 * It's defined here for Intelephense support when the package is not yet installed.
 */
class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
        ];
    }
}

namespace Livewire\Volt;

/**
 * Livewire Volt class for creating Volt routes.
 *
 * This class is provided by the livewire/volt package.
 * It's defined here for Intelephense support when the package is not yet installed.
 */
class Volt
{
    /**
     * Create a new Volt route.
     *
     * @param  string  $uri
     * @param  string  $view
     * @return \Illuminate\Routing\Route
     */
    public static function route(string $uri, string $view)
    {
        // Stub implementation for Intelephense
    }
}
