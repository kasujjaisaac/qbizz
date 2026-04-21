<?php

namespace App\Http\Controllers;

use App\Models\BusinessProfile;
use App\Models\User;

abstract class Controller
{
    protected function lockBusinessProfileForUpdate(User $user): BusinessProfile
    {
        abort_unless($user->business_profile_id !== null, 409, 'A business profile is required before you can continue.');

        return BusinessProfile::query()->lockForUpdate()->findOrFail($user->business_profile_id);
    }
}
