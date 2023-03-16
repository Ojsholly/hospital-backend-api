<?php

namespace App\Traits;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait HasRoles
{
    public static function role(string|array $role): Builder|User
    {
        if (! is_array($role)) {
            $role = [$role];
        }

        return self::whereHas('roles', fn ($query) => $query->whereIn('name', $role));
    }

    public function hasRole(string $role): bool
    {
        return $this->roles->contains('name', $role);
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->roles->whereIn('name', $roles)->isNotEmpty();
    }

    public function getRoleNamesAttribute(): array
    {
        return $this->roles->pluck('name')->toArray();
    }

    public function assignRole(string|array $roles): void
    {
        if (! is_array($roles)) {
            $roles = [$roles];
        }

        collect($roles)->each(fn ($role) => $this->roles()->attach(Role::where('name', $role)->first()));
    }

    public function doesNotHaveRole(string $role): bool
    {
        return ! $this->hasRole($role);
    }
}
