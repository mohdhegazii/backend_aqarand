<?php

namespace App\Policies;

use App\Models\MediaFile;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MediaFilePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->is_admin;
    }

    public function view(User $user, MediaFile $mediaFile)
    {
        return $user->is_admin;
    }

    public function create(User $user)
    {
        return $user->is_admin;
    }

    public function update(User $user, MediaFile $mediaFile)
    {
        return $user->is_admin;
    }

    public function delete(User $user, MediaFile $mediaFile)
    {
        return $user->is_admin;
    }
}
