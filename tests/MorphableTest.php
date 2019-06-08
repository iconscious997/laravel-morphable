<?php

namespace Ahnify\Morphable\Test;

class MorphableTest extends TestCase
{
    /** @test */
    public function it_can_filter_one_morph_type()
    {
        $comments = Comment::whereMorphable('commentable', Post::class, function ($query) {
            $query->whereId(13);
        })->get();
        $this->assertEquals(1, $comments->count());
        $this->assertEquals(1, $comments->where('commentable_type', Post::class)->count());

        $comments = Comment::whereMorphable('commentable', Post::class, function ($query) {
            $query->where('id', '>', 10);
        })->get();
        $this->assertEquals(10, $comments->count());
        $this->assertEquals(10, $comments->where('commentable_type', Post::class)->count());
        $this->assertEquals(0, $comments->where('commentable_type', Video::class)->count());
    }

    /** @test */
    public function it_can_filter_multiple_morph_types()
    {
        $comments = Comment::whereMorphable('commentable', [Post::class, Video::class], function ($query) {
            $query->whereId(13);
        })->get();
        $this->assertEquals(2, $comments->count());
        $this->assertEquals(1, $comments->where('commentable_type', Post::class)->count());
        $this->assertEquals(1, $comments->where('commentable_type', Video::class)->count());

        $comments = Comment::whereMorphable('commentable', [Post::class, Video::class], function ($query) {
            $query->where('title', 'like', '%2%');
        })->get();
        $this->assertEquals(6, $comments->count());
        $this->assertEquals(3, $comments->where('commentable_type', Post::class)->count());
        $this->assertEquals(3, $comments->where('commentable_type', Video::class)->count());
    }

    /** @test */
    public function it_can_filter_chained_morph_multiple_types()
    {
        $comments = Comment::whereMorphable('commentable', Post::class, function ($query) {
            $query->whereId(13);
        })->orWhereMorphable('commentable', Video::class, function ($query) {
            $query->whereTitle('video 20');
        })->get();
        $this->assertEquals(2, $comments->count());
        $this->assertEquals(1, $comments->where('commentable_type', Post::class)->count());
        $this->assertEquals(1, $comments->where('commentable_type', Video::class)->count());
        $this->assertTrue($comments->where('commentable_type', Post::class)->where('commentable_id', 13)->isNotEmpty());
        $this->assertTrue($comments->where('commentable_type', Video::class)->where('commentable_id', 20)->isNotEmpty());
    }

    /** @test */
    public function it_can_filter_custom_morph_types()
    {
        $comments = Comment::whereMorphable('commentable', 'images', function ($query) {
        })->get();
        $this->assertEquals(20, $comments->count());
    }
}
