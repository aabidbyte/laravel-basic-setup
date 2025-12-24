<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Constants\DataTable\DataTableUi;
use App\Enums\DataTable\DataTableColumnType;
use App\Enums\DataTable\DataTableFilterType;
use App\Models\Base\BaseUserModel;
use App\Models\Concerns\HasDataTable;
use App\Services\DataTable\Dsl\BulkActionItem;
use App\Services\DataTable\Dsl\ColumnItem;
use App\Services\DataTable\Dsl\FilterItem;
use App\Services\DataTable\Dsl\HeaderItem;
use App\Services\DataTable\Dsl\RowActionItem;
use App\Services\DataTable\OptionsProviders\RoleOptionsProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends BaseUserModel
{
    use HasDataTable, HasRoles, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'team_id',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'frontend_preferences' => 'array',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Get the teams that the user belongs to.
     */
    public function teams(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_user')
            ->withTimestamps();
    }

    /**
     * Get the user's primary team.
     */
    public function team(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Find user by identifier (email or username).
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function findByIdentifier(string $identifier)
    {
        return static::query()
            ->where('email', $identifier)
            ->orWhere('username', $identifier);
    }

    /**
     * Get the email address for password reset.
     *
     * This method is used by Laravel's password reset system to identify the user
     * and send the password reset notification. Password reset notifications require
     * an email address, so users without email cannot use the standard password
     * reset flow.
     *
     * For token storage, we use 'identifier' (email or username) to support users
     * with optional email addresses, but the notification system requires an email.
     */
    public function getEmailForPasswordReset(): string
    {
        if (empty($this->email)) {
            throw new \RuntimeException('User must have an email address to reset password via email notification.');
        }

        return $this->email;
    }

    /**
     * Update the last login timestamp
     *
     * Overrides the base method to handle MySQL trigger for user ID 1.
     * This is a system update that should always be allowed, even for user ID 1.
     */
    public function updateLastLoginAt(): bool
    {
        // Handle MySQL trigger for user ID 1 (system updates like last_login_at should always be allowed)
        if ($this->id === 1 && DB::getDriverName() === 'mysql' && ! isTesting()) {
            DB::statement('SET @laravel_user_id_1_self_edit = 1');
        }

        $result = parent::updateLastLoginAt();

        // Clear the session variable after update (MySQL only)
        if ($this->id === 1 && DB::getDriverName() === 'mysql' && ! isTesting()) {
            DB::statement('SET @laravel_user_id_1_self_edit = NULL');
        }

        return $result;
    }

    /**
     * Get the DataTable definition for User model
     */
    public static function datatable(): \App\Services\DataTable\Dsl\DataTableDefinition
    {
        return \App\Services\DataTable\Dsl\DataTableDefinition::make()
            ->headers(
                HeaderItem::make()
                    ->label(__('ui.table.users.name'))
                    ->sortable('name')
                    ->column(
                        ColumnItem::make()
                            ->name('name')
                            ->type(DataTableColumnType::TEXT)
                            ->props(['bold' => true])
                    ),
                HeaderItem::make()
                    ->label(__('ui.table.users.email'))
                    ->sortable('email')
                    ->column(
                        ColumnItem::make()
                            ->name('email')
                            ->type(DataTableColumnType::TEXT)
                            ->props(['muted' => true])
                    ),
                HeaderItem::make()
                    ->label(__('ui.table.users.verified'))
                    ->sortable('email_verified_at')
                    ->column(
                        ColumnItem::make()
                            ->name('email_verified_at')
                            ->type(DataTableColumnType::BOOLEAN)
                            ->props([
                                'trueLabel' => __('ui.table.users.verified_yes'),
                                'falseLabel' => __('ui.table.users.verified_no'),
                            ])
                    ),
                HeaderItem::make()
                    ->label(__('ui.table.users.created_at'))
                    ->sortable('created_at')
                    ->column(
                        ColumnItem::make()
                            ->name('created_at')
                            ->type(DataTableColumnType::DATE)
                            ->props(['format' => 'Y-m-d', 'muted' => true])
                    )
            )
            ->actions(
                RowActionItem::make()
                    ->key(DataTableUi::ACTION_VIEW)
                    ->label(__('ui.actions.view'))
                    ->icon(DataTableUi::ICON_EYE)
                    ->variant(DataTableUi::VARIANT_GHOST),
                RowActionItem::make()
                    ->key(DataTableUi::ACTION_EDIT)
                    ->label(__('ui.actions.edit'))
                    ->icon(DataTableUi::ICON_PENCIL)
                    ->variant(DataTableUi::VARIANT_GHOST),
                RowActionItem::make()
                    ->key(DataTableUi::ACTION_DELETE)
                    ->label(__('ui.actions.delete'))
                    ->icon(DataTableUi::ICON_TRASH)
                    ->variant(DataTableUi::VARIANT_GHOST)
                    ->color(DataTableUi::COLOR_ERROR)
                    ->showModal(DataTableUi::MODAL_TYPE_CONFIRM)
                    ->execute(function (User $user) {
                        $user->delete();
                    })
            )
            ->bulkActions(
                BulkActionItem::make()
                    ->key(DataTableUi::BULK_ACTION_ACTIVATE)
                    ->label(__('ui.actions.activate_selected'))
                    ->icon(DataTableUi::ICON_UNLOCK)
                    ->variant(DataTableUi::VARIANT_GHOST)
                    ->execute(function (\Illuminate\Database\Eloquent\Collection $users) {
                        User::whereIn('uuid', $users->pluck('uuid'))->update(['is_active' => true]);
                    }),
                BulkActionItem::make()
                    ->key(DataTableUi::BULK_ACTION_DEACTIVATE)
                    ->label(__('ui.actions.deactivate_selected'))
                    ->icon(DataTableUi::ICON_LOCK)
                    ->variant(DataTableUi::VARIANT_GHOST)
                    ->execute(function (\Illuminate\Database\Eloquent\Collection $users) {
                        User::whereIn('uuid', $users->pluck('uuid'))->update(['is_active' => false]);
                    }),
                BulkActionItem::make()
                    ->key(DataTableUi::BULK_ACTION_DELETE)
                    ->label(__('ui.actions.delete_selected'))
                    ->icon(DataTableUi::ICON_TRASH)
                    ->variant(DataTableUi::VARIANT_GHOST)
                    ->color(DataTableUi::COLOR_ERROR)
                    ->showModal(DataTableUi::MODAL_TYPE_CONFIRM)
                    ->execute(function (\Illuminate\Database\Eloquent\Collection $users) {
                        User::whereIn('uuid', $users->pluck('uuid'))->delete();
                    })
            )
            ->filters(
                FilterItem::make()
                    ->key('role')
                    ->label(__('ui.table.users.filters.role'))
                    ->placeholder(__('ui.table.users.filters.all_roles'))
                    ->type(DataTableFilterType::SELECT)
                    ->optionsProvider(RoleOptionsProvider::class)
                    ->relationship(['name' => 'roles', 'column' => 'name']),
                FilterItem::make()
                    ->key('is_active')
                    ->label(__('ui.table.users.filters.status'))
                    ->placeholder(__('ui.table.users.filters.all_status'))
                    ->type(DataTableFilterType::SELECT)
                    ->options([
                        ['value' => '1', 'label' => __('ui.table.users.status_active')],
                        ['value' => '0', 'label' => __('ui.table.users.status_inactive')],
                    ])
                    ->valueMapping(['1' => true, '0' => false]),
                FilterItem::make()
                    ->key('email_verified_at')
                    ->label(__('ui.table.users.filters.verified'))
                    ->placeholder(__('ui.table.users.filters.all_status'))
                    ->type(DataTableFilterType::SELECT)
                    ->options([
                        ['value' => '1', 'label' => __('ui.table.users.verified_yes')],
                        ['value' => '0', 'label' => __('ui.table.users.verified_no')],
                    ])
                    ->fieldMapping('email_verified_at')
                    ->valueMapping(['1' => 'not_null', '0' => 'null']),
                FilterItem::make()
                    ->key('created_at')
                    ->label(__('ui.table.users.filters.created_at'))
                    ->type(DataTableFilterType::DATE_RANGE)
                    ->props(['column' => 'created_at'])
            );
    }
}
