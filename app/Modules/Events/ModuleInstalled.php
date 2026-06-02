<?php

namespace App\Modules\Events;

use App\Modules\Contracts\ModuleInterface;
use Illuminate\Foundation\Events\Dispatchable;

class ModuleInstalled
{
    use Dispatchable;

    public function __construct(public readonly ModuleInterface $module) {}
}
