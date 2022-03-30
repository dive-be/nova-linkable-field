<?php

namespace Dive\LinkableField\Models;

use Illuminate\Database\Eloquent\Relations\MorphPivot;

class LinkablePivot extends MorphPivot
{
    protected $table = 'model_linkables';
}