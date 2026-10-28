<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Column;
use App\Admin\Field;
use App\Admin\ResourceController;
use App\Models\User;
use App\Notifications\DashboardAccountCreated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserController extends ResourceController
{
    protected string $model = User::class;

    protected string $routeName = 'admin.users';

    protected string $title = 'Users & Roles';

    protected string $singular = 'User';

    protected array $searchable = ['name', 'email'];

    protected array $with = ['roles'];

    protected function description(): ?string
    {
        return 'Team members who can sign in to the dashboard. Give each person only the role they need.';
    }

    protected function columns(): array
    {
        return [Column::make('name', 'Name'), Column::make('email', 'Email'), Column::make('role_names', 'Roles'), Column::make('created_at', 'Added')];
    }

    protected function row(Model $record): array
    {
        return array_merge(parent::row($record), ['role_names' => $record->roles->pluck('name')->map(fn ($r) => str($r)->replace('-', ' ')->title())->implode(', ') ?: '—']);
    }

    protected function fields(?Model $record): array
    {
        return [
            Field::text('name', 'Name')->required()->half(),
            Field::email('email', 'Email')->required()->half(),
            Field::make('password', 'password', $record ? 'New password (leave blank to keep)' : 'Password (optional)')->half()->set('virtual', true)
                ->help($record ? '' : 'Leave blank to email them a link to choose their own password.'),
            Field::multiselect('roles', 'Roles', Role::orderBy('name')->get()->map(fn ($r) => ['value' => $r->name, 'label' => str($r->name)->replace('-', ' ')->title()->toString()])->all())->half()
                ->help('Owner: full access. Content manager: publishing only. Operations manager: orders, bookings and enquiries.'),
        ];
    }

    protected function rules(?Model $record): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users')->ignore($record)],
            'password' => ['nullable', Password::min(10)->mixedCase()->numbers()],
            'roles' => ['array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ];
    }

    protected function readValue(Model $record, Field $field): mixed
    {
        return match ($field->name) {
            'roles' => $record->roles->pluck('name')->all(),
            'password' => '',
            default => parent::readValue($record, $field),
        };
    }

    protected function beforeSave(Model $record, array $data, Request $request): void
    {
        if (filled($data['password'] ?? null)) {
            $record->password = $data['password'];
        } elseif (! $record->exists) {
            // Unusable until they set their own via the emailed link.
            $record->password = Str::random(40);
        }
        $record->email_verified_at ??= now();
    }

    protected function afterSave(Model $record, array $data, Request $request): void
    {
        $roles = $data['roles'] ?? [];

        // Never let someone lock themselves (or the last owner) out of the dashboard.
        if ($record->is($request->user()) && $record->hasRole('owner') && ! in_array('owner', $roles, true)) {
            $roles[] = 'owner';
        }

        $record->syncRoles($roles);

        if ($record->wasRecentlyCreated) {
            $record->notify(new DashboardAccountCreated(PasswordBroker::broker()->createToken($record), $request->user()->name));
        }
    }

    public function destroy(string $record): RedirectResponse
    {
        abort_if((string) auth()->id() === $record, 422, 'You cannot delete your own account.');

        return parent::destroy($record);
    }
}
