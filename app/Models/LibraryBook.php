<?php

namespace App\Models;

use App\Models\Concerns\RecordsAuditTrail;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LibraryBook extends BaseModel
{
    use RecordsAuditTrail, SoftDeletes;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'tenant_id',
        'title',
        'author',
        'isbn',
        'category',
        'copies_total',
        'copies_available',
        'shelf_location',
        'status',
    ];

    public function loans(): HasMany
    {
        return $this->hasMany(LibraryLoan::class);
    }
}
