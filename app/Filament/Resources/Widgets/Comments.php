<?php

namespace App\Filament\Resources\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;

class Comments extends Widget
{
    protected static string $view = 'filament.resources.widgets.comments';

    protected int | string | array $columnSpan = 'full';

    public Model $record;
}
