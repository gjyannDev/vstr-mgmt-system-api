<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name'])]
class Location extends Model
{
  use HasFactory;

  public function admins(): BelongsToMany
  {
    return $this->belongsToMany(User::class, 'admin_locations')
      ->withTimestamps();
  }

  public function kiosks(): HasMany
  {
    return $this->hasMany(Kiosk::class);
  }
}
