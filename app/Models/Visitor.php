<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['tenant_id', 'location_id', 'full_name', 'email', 'phone', 'company', 'photo_url', 'id_number'])]
class Visitor extends Model
{
  use HasFactory, HasUuids;

  protected $keyType = 'string';

  public $incrementing = false;

  protected function casts(): array
  {
    return [];
  }

  public function visits(): HasMany
  {
    return $this->hasMany(Visit::class);
  }

  public function location(): BelongsTo
  {
    return $this->belongsTo(Location::class);
  }
}
