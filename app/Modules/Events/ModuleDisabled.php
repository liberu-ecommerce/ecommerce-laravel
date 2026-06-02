<?php

namespace App\Modules\Events;

use App\Modules\Contracts\ModuleInterface;
use Illuminate\Foundation\Events\Dispatchable;

class ModuleDisabled
{
    use Dispatchable;

    public function __construct(public readonly ModuleInterface $module) {}
}
