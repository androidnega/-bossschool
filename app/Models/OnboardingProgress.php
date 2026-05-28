<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboardingProgress extends Model
{
    use HasFactory;

    protected $table = 'tenant_onboarding_progress';

    protected $fillable = [
        'tenant_id',
        'current_step',
        'completed_steps',
        'payload',
        'updated_by_user_id',
        'finished_at',
    ];

    protected $casts = [
        'completed_steps' => 'array',
        'payload' => 'array',
        'finished_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function markStepDone(string $key): self
    {
        $done = (array) ($this->completed_steps ?? []);
        if (! in_array($key, $done, true)) {
            $done[] = $key;
        }
        $this->completed_steps = $done;

        return $this;
    }
}
