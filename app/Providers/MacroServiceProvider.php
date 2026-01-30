<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\ServiceProvider;

class MacroServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerSearchMacros();
    }

    /**
     * Register global search macros for Eloquent Builder
     */
    private function registerSearchMacros(): void
    {
        /**
         * Simple search macro for single/multiple columns
         *
         * Usage:
         * User::search('john', ['name', 'email'])
         * User::search('john', 'name')
         * Team::search('marketing', ['name', 'description'])
         */
        Builder::macro('search', function (string $query, array|string $columns = []) {
            /** @var Builder $this */
            if (empty($query) || empty($columns)) {
                return $this;
            }

            $columns = \is_array($columns) ? $columns : [$columns];

            return $this->where(function (Builder $builder) use ($query, $columns) {
                foreach ($columns as $column) {
                    // Handle relation.column syntax
                    if (\str_contains($column, '.')) {
                        [$relation, $relationColumn] = \explode('.', $column, 2);
                        $builder->orWhereHas($relation, function (Builder $relationQuery) use ($relationColumn, $query) {
                            $relationQuery->where($relationColumn, 'LIKE', "%{$query}%");
                        });
                    } else {
                        $builder->orWhere($column, 'LIKE', "%{$query}%");
                    }
                }
            });
        });
    }
}
