<?php


namespace Ahnify\Morphable\Helpers;


use Illuminate\Database\Eloquent\Builder as EloquentQueryBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as DatabaseQueryBuilder;

class Morphable
{
    /**
     * @var MorphTo
     */
    private $polymorphicRelation;
    /**
     * @var callable
     */
    private $callback;
    /**
     * @var string
     */
    private $method;
    /**
     * @var array
     */
    private $morphedRelations;
    /**
     * @var EloquentQueryBuilder
     */
    private $query;


    /**
     * Morphable constructor.
     * @param EloquentQueryBuilder $query
     * @param MorphTo $polymorphicRelation
     * @param callable $callback
     * @param array $morphedRelations
     * @param string $method
     */
    public function __construct(EloquentQueryBuilder $query, MorphTo $polymorphicRelation, callable $callback, array $morphedRelations, string $method)
    {
        $this->query = $query;
        $this->polymorphicRelation = $polymorphicRelation;
        $this->callback = $callback;
        $this->morphedRelations = $morphedRelations;
        $this->method = $method;
    }

    /**
     * iterate on all polymorphic relations and applying callback on them
     */
    public function applyFilter()
    {
        $this->query->{$this->method}(function($query){
            collect($this->morphedRelations)->each(function($type,$index) use($query){
                $method = $index ? 'orWhere' : 'where';
                $query->{$method}(function($query)use($type){
                    $this->addWhereMorph($query, $type);
                });
            });
        });
    }
    /**
     * Create EloquentQueryBuilder from DatabaseQueryBuilder & Apply query on that
     * @param string $class
     * @param DatabaseQueryBuilder $query
     */
    private function applyCallback(string $class, DatabaseQueryBuilder $query)
    {
        /** @var Model $model */
        $model = new $class;
        /** @var EloquentQueryBuilder $query */
        $query = $model->newEloquentBuilder($query)->setModel($model);
        ($this->callback)($query);
        $query->select([$model->getKeyName()]);
    }


    /**
     * filter base on polymorphic relation type
     * @param EloquentQueryBuilder $query
     * @param string $type
     */
    private function addWhereMorph(EloquentQueryBuilder $query, string $type)
    {
        $class = Relation::getMorphedModel($type) ?? $type;
        $query->where($this->polymorphicRelation->getMorphType(), '=', $type)
            ->whereIn($this->polymorphicRelation->getForeignKeyName(), function ($query) use ($class) {
                $this->applyCallback($class,$query);
            });
    }


    /**
     * @param $query
     * @param MorphTo $polymorphicRelation
     * @param $callback
     * @param array $morphedRelations
     * @param $method
     */
    public static function filterPolymorphicRelation(EloquentQueryBuilder $query, MorphTo $polymorphicRelation,callable $callback, array $morphedRelations, string $method)
    {
        (
            new self(...func_get_args())
        )->applyFilter();
    }



}