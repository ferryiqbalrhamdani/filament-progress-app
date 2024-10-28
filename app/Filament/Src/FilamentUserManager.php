<?php

namespace App\Filament\Src;

use Filament\FilamentManager;
use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;

class FilamentUserManager extends FilamentManager
{
    public function getUserName(Model | Authenticatable $user): string
    {
        if ($user instanceof HasName) {
            return $user->getFilamentName();
        }

        return $user->getAttributeValue('username');
    }
}
