<?php

namespace App\Models\Concerns;

use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;

/**
 * Attach this trait to a model to automatically write before/after audit log
 * entries for create, update, soft-delete and restore events.
 *
 * Optional model hooks:
 *   - public function auditAction(string $event): string  // override action key
 *   - public function auditDescription(string $event): ?string
 *   - public array $auditHidden  // attribute names to redact (e.g. ['password'])
 */
trait RecordsAuditTrail
{
    protected static function bootRecordsAuditTrail(): void
    {
        static::created(function (Model $model): void {
            self::writeAuditEntry($model, 'created', null, self::sanitize($model, $model->getAttributes()));
        });

        static::updated(function (Model $model): void {
            $changed = $model->getChanges();

            if (empty($changed)) {
                return;
            }

            $before = [];
            foreach (array_keys($changed) as $key) {
                $before[$key] = $model->getOriginal($key);
            }

            self::writeAuditEntry(
                $model,
                'updated',
                self::sanitize($model, $before),
                self::sanitize($model, $changed)
            );
        });

        static::deleted(function (Model $model): void {
            $event = method_exists($model, 'isForceDeleting') && $model->isForceDeleting()
                ? 'force_deleted'
                : 'deleted';

            self::writeAuditEntry($model, $event, self::sanitize($model, $model->getOriginal()), null);
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function (Model $model): void {
                self::writeAuditEntry($model, 'restored', null, self::sanitize($model, $model->getAttributes()));
            });
        }
    }

    /**
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     */
    protected static function writeAuditEntry(Model $model, string $event, ?array $before, ?array $after): void
    {
        $logger = app(ActivityLogger::class);

        $entity = strtolower(class_basename($model));
        $action = method_exists($model, 'auditAction')
            ? (string) $model->auditAction($event)
            : $entity.'_'.$event;

        $description = method_exists($model, 'auditDescription')
            ? $model->auditDescription($event)
            : null;

        $metadata = [];
        if ($before !== null) {
            $metadata['before'] = $before;
        }
        if ($after !== null) {
            $metadata['after'] = $after;
        }

        $tenantId = $model->getAttribute('tenant_id');

        $logger->log(
            $action,
            $description,
            $metadata,
            $tenantId !== null ? (int) $tenantId : null,
            $model::class,
            (int) $model->getKey()
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    protected static function sanitize(Model $model, array $attributes): array
    {
        $hidden = property_exists($model, 'auditHidden') ? (array) $model->auditHidden : [];
        $hidden = array_merge($hidden, ['password', 'remember_token']);

        foreach ($hidden as $key) {
            if (array_key_exists($key, $attributes)) {
                $attributes[$key] = '***';
            }
        }

        return $attributes;
    }
}
