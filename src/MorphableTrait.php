<?php

namespace Ahnify\Morphable;

use Ahnify\Morphable\Helpers\Morphable;
use Illuminate\Database\Eloquent\Relations\MorphTo;

trait MorphableTrait
{
    public function scopeWhereMorphable($query, $name, $type, $callback)
    {
        /** @var MorphTo $relation */
        $relation = $this->{$name}();
        $type = (array) $type;
        Morphable::filterPolymorphicRelation($query, $relation, $callback, $type, 'where');
    }

    public function scopeOrWhereMorphable($query, $name, $type, $callback)
    {
        /** @var MorphTo $relation */
        $relation = $this->{$name}();
        $type = (array) $type;
        Morphable::filterPolymorphicRelation($query, $relation, $callback, $type, 'orWhere');
    }
}
