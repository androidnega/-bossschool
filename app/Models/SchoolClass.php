<?php

namespace App\Models;

use App\Models\Concerns\RecordsAuditTrail;

class SchoolClass extends BaseModel
{
    use RecordsAuditTrail;

    /**
     * Standard Ghanaian Primary + JHS class names.
     *
     * Free-text input is still allowed for schools that label things
     * differently (e.g. "Form 1", "Year 4 Blue"); these are the
     * defaults the UI exposes via an autocomplete datalist.
     *
     * @var array<int, string>
     */
    public const GHANA_SUGGESTIONS = [
        'Nursery 1',
        'Nursery 2',
        'KG 1',
        'KG 2',
        'Basic 1',
        'Basic 2',
        'Basic 3',
        'Basic 4',
        'Basic 5',
        'Basic 6',
        'JHS 1',
        'JHS 2',
        'JHS 3',
    ];

    protected $table = 'classes';

    protected $fillable = [
        'tenant_id',
        'name',
        'section',
    ];
}
