<?php

namespace App\Policies;

use App\Models\Package;
use App\Models\Seller;
use Illuminate\Auth\Access\HandlesAuthorization;

class PackagePolicy
{
    use HandlesAuthorization;

    /**
     * A seller may only view their own packages.
     */
    public function view(Seller $seller, Package $package): bool
    {
        return $this->owns($seller, $package);
    }

    /**
     * A seller may only update their own packages.
     */
    public function update(Seller $seller, Package $package): bool
    {
        return $this->owns($seller, $package);
    }

    /**
     * A seller may only delete their own packages.
     */
    public function delete(Seller $seller, Package $package): bool
    {
        return $this->owns($seller, $package);
    }

    private function owns(Seller $seller, Package $package): bool
    {
        return (int) $package->seller_id === (int) $seller->id;
    }
}
