<?php


namespace Ahnify\Morphable\Helpers;


use Illuminate\Database\Eloquent\Builder as EloquentQueryBuilder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as DatabaseQueryBuilder;

class Morphable
{

    /**
     * Create EloquentQueryBuilder from DatabaseQueryBuilder & Apply query on that
     * @param string $class
     * @param DatabaseQueryBuilder $query
     * @param $callback
     */
    private static function applyCallback(string $class, DatabaseQueryBuilder $query, $callback)
    {
        $model = new $class;
        /** @var EloquentQueryBuilder $query */
        $query = $model->newEloquentBuilder($query)->setModel($model);
        $callback($query);
        $query->select([$model->getKeyName()]);
    }

    /**
     * filter base on polymorphic relation type
     * @param EloquentQueryBuilder $query
     * @param string $type
     * @param MorphTo $polymorphicRelation
     * @param $callback
     */
    private static function addWhereMorph(EloquentQueryBuilder $query, string $type, MorphTo $polymorphicRelation, $callback)
    {
        $class = Relation::getMorphedModel($type) ?? $type;
        $query->where($polymorphicRelation->getMorphType(), '=', $type)
            ->whereIn($polymorphicRelation->getForeignKeyName(), function ($query) use ($class, $callback) {
                self::applyCallback($class,$query,$callback);
            });
    }

    /**
     * iterate on all polymorphic relations and applying callback on them
     * @param $query
     * @param MorphTo $polymorphicRelation
     * @param $callback
     * @param array $morphedRelations
     * @param $method
     */
    public static function filterPolymorphicRelation($query, MorphTo $polymorphicRelation, $callback, array $morphedRelations, $method)
    {
        $query->{$method}(function($query) use($polymorphicRelation, $callback,$morphedRelations){
            collect($morphedRelations)->each(function($type,$index)use($query, $polymorphicRelation, $callback){
                $method = $index ? 'orWhere' : 'where';
                $query->{$method}(function($query)use($type,$polymorphicRelation, $callback){
                    self::addWhereMorph($query, $type, $polymorphicRelation, $callback);
                });
            });
        });

    }
}