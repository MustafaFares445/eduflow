<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Data\Notification\NotificationTemplateData;
use App\Models\NotificationTemplate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

final class NotificationTemplateService
{
    public function index(int $perPage = 20, ?string $search = null): LengthAwarePaginator
    {
        $query = QueryBuilder::for(NotificationTemplate::query())
            ->allowedFilters(
                AllowedFilter::exact('isActive', 'is_active'),
            )
            ->allowedSorts(
                AllowedSort::field('key'),
                AllowedSort::field('created_at'),
            );

        if ($search !== null && $search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('key', 'like', '%'.$search.'%')
                    ->orWhere('title', 'like', '%'.$search.'%')
                    ->orWhere('body', 'like', '%'.$search.'%');
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function show(NotificationTemplate $notificationTemplate): NotificationTemplate
    {
        return $notificationTemplate;
    }

    public function store(NotificationTemplateData $data): NotificationTemplate
    {
        $template = new NotificationTemplate();
        $template->fill([
            'key' => $data->key,
            'title' => $data->title,
            'body' => $data->body,
            'channels' => $data->channels,
            'variables' => $data->variables,
            'is_active' => $data->isActive,
        ]);
        $template->save();

        return $template->fresh();
    }

    public function update(NotificationTemplate $notificationTemplate, NotificationTemplateData $data): NotificationTemplate
    {
        $notificationTemplate->fill([
            'key' => $data->key,
            'title' => $data->title,
            'body' => $data->body,
            'channels' => $data->channels,
            'variables' => $data->variables,
            'is_active' => $data->isActive,
        ]);
        $notificationTemplate->save();

        return $notificationTemplate->fresh();
    }

    public function delete(NotificationTemplate $notificationTemplate): void
    {
        $notificationTemplate->delete();
    }
}
