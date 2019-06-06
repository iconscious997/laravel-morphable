<?php


namespace Ahnify\Morphable\Test;


use Ahnify\Morphable\MorphableTrait;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use MorphableTrait;
    protected $guarded = [];
    public function commentable()
    {
        return $this->morphTo();
    }
}