<?php

namespace App\Features\Location\Repository;

use App\Models\Location;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class LocationRepository extends BaseRepository
{
  public function __construct(Location $model)
  {
    $this->model = $model;
  }

  public function list(?string $search = null): Collection
  {
    $query = $this->model->newQuery()->latest('created_at');

    if ($search !== null && $search !== '') {
      $query->where('name', 'like', '%' . $search . '%');
    }

    /** @var Collection<int, Location> $locations */
    $locations = $query->get();

    return $locations;
  }

  public function createLocation(array $data): Location
  {
    /** @var Location $location */
    $location = $this->create($data);

    return $location;
  }

  public function updateLocation(Location $location, array $data): Location
  {
    $location->update($data);

    /** @var Location $fresh */
    $fresh = $location->fresh();

    return $fresh;
  }

  public function deleteLocation(Location $location): bool
  {
    return (bool) $location->delete();
  }
}
