<?php

namespace Dive\LinkableField;

use Illuminate\Database\Eloquent\Relations\MorphPivot;

class LinkablePivot extends MorphPivot
{
    protected $table = 'url_linkables';
}