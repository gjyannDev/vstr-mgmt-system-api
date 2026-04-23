<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['tenant_id', 'location_id', 'name', 'description', 'requires_approval', 'active', 'is_camera_active'])]
class VisitType extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'requires_approval' => 'boolean',
            'active' => 'boolean',
            'is_camera_active' => 'boolean',
        ];
    }

    public function formFields(): HasMany
    {
        return $this->hasMany(FormField::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function kiosks(): BelongsToMany
    {
        return $this->belongsToMany(Kiosk::class, 'kiosk_visit_types', 'visit_type_id', 'kiosk_id');
    }
}
