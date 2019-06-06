<?php

namespace Ahnify\Morphable\Test;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();

        $this->setCustomMorphType();
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [

        ];
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

    }

    protected function setUpDatabase()
    {
        $this->app['db']->connection()->getSchemaBuilder()->create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->timestamps();
        });
        $this->app['db']->connection()->getSchemaBuilder()->create('videos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->timestamps();
        });
        $this->app['db']->connection()->getSchemaBuilder()->create('images', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->timestamps();
        });
        $this->app['db']->connection()->getSchemaBuilder()->create('comments', function (Blueprint $table) {
            $table->increments('id');
            $table->morphs('commentable');
            $table->string('description');
            $table->timestamps();

        });

        collect(range(1, 20))->each(function (int $i) {
            $post = Post::create([
                'title' => "post {$i}",
            ]);
            $video = Video::create([
                'title' => "video {$i}",
            ]);
            $image = Image::create([
                'title' => "video {$i}",
            ]);
            Comment::create([
                'commentable_type' => Post::class,
                'commentable_id' => $post->getKey(),
                'description' => "post comment {$i}",
            ]);
            Comment::create([
                'commentable_type' => Video::class,
                'commentable_id' => $video->getKey(),
                'description' => "video comment {$i}",
            ]);
            Comment::create([
                'commentable_type' => 'images',
                'commentable_id' => $image->getKey(),
                'description' => "image comment {$i}",
            ]);
        });
    }

    private function setCustomMorphType()
    {
        Relation::morphMap([
            'images' => Image::class,
        ]);
    }

}