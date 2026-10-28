<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Column;
use App\Admin\ResourceController;
use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditLogController extends ResourceController
{
    protected string $model = AuditLog::class;

    protected string $routeName = 'admin.audit-logs';

    protected string $title = 'Audit Log';

    protected string $singular = 'Entry';

    protected array $searchable = ['description', 'action'];

    protected array $with = ['user'];

    protected bool $creatable = false;

    protected bool $deletable = false;

    protected function description(): ?string
    {
        return 'A record of important administrative and financial actions.';
    }

    protected function columns(): array
    {
        return [Column::make('created_at', 'When'), Column::make('user.name', 'User'), Column::make('action', 'Action'), Column::make('description', 'Description'), Column::make('ip_address', 'IP')];
    }

    protected function hasForm(): bool
    {
        return false;
    }

    protected function fields(?Model $record): array
    {
        return [];
    }

    protected function rules(?Model $record): array
    {
        return [];
    }
}
