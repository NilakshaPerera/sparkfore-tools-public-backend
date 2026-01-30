<?php

namespace App\Domain\Repositories\Command;

use App\Domain\Models\Installation;
use Illuminate\Database\Eloquent\Collection;

class CommandRepository implements CommandRepositoryInterface
{
    public function __construct(private Installation $installation)
    {}

    public function getInstallation($id)
    {
        return $this->installation::where('id', $id)->first();
    }

    /**
     * @return Collection
     */
    public function getInstallations()
    {
        return $this->installation::all();
    }
}
