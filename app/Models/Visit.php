<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['tenant_id', 'location_id', 'visitor_id', 'host_id', 'visit_type_id', 'purpose', 'status', 'check_in_at', 'check_out_at', 'check_in_by', 'check_out_by', 'qr_code', 'notes', 'session_key'])]
class Visit extends Model
{
  use HasFactory, HasUuids;

  protected $keyType = 'string';

  public $incrementing = false;

  protected function casts(): array
  {
    return [
      'check_in_at' => 'datetime',
      'check_out_at' => 'datetime',
    ];
  }

  public function visitor(): BelongsTo
  {
    return $this->belongsTo(Visitor::class);
  }

  public function visitType(): BelongsTo
  {
    return $this->belongsTo(VisitType::class);
  }

  public function location(): BelongsTo
  {
    return $this->belongsTo(Location::class);
  }
}
