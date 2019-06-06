# Morphable

This package provides a trait that adds query scopes to an Eloquent model for easy querying on polymorphic relations.


## Installation

You can install this package via composer using this command:

```bash
composer require "ahnify/laravel-morphable:^0.1"
```

The package doesn't need to be registered.

## Usage
To query on polymorphic relation  you must:
1. add the trait `Ahnify\Morphable\MorphableTrait` to your model.

### Example

```php
use Ahnify\Morphable\MorphableTrait

class ExampleModel extends Eloquent
{

    use MorphableTrait;

    ...
}
```
that's it.

now you can query on your relation like this:

```php
ExampleModel::whereMorphable('transactionable',BankTransaction::class,function($query){
    $query->where('amount','>', 30 );
})->get()
```

this is equivalent to this for non polymorphic relations:

```php
ExampleModel::whereHas('bankTransaction',function($query){
    $query->where('amount','>', 30 );
})->get()
```
also, we can chain it too:
```php
ExampleModel::query()
    ->whereMorphable('transactionable',BankTransaction::class,function($query){
        $query->where('amount','>', 30 );
    })
    ->orWhereMorphable('transactionable',OnlineTransaction::class,function($query){
            $query->where('created_date','>', '2019-01-01' );
    })
    ->get()
```
also, if some of your polymorphic related to our model have common attributes, then we can query on them and pass an array of morphed class type like this:
```php
ExampleModel::whereMorphable('transactionable',[BankTransaction::class,OnlineTransaction::class],function($query){
    $query->where('amount','>', 30 );
})->get()
// get all rows that have bank or online transactions that has amount more than 30  
```
in this example the result includes all examples that has bank or online transaction with has amount more than 30  

this works also with custom types too : 
 ```php
 ExampleModel::whereMorphable('transactionable',['bankTransaction','onlineTransaction'],function($query){
     $query->where('amount','>', 30 );
 })->get()
 ```