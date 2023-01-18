<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug'];

    /**
     * Relationship between tag with posts
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }

    /**
     * Add Tags
     *
     * @param array $tagNames
     * @return Collection
     */
    public static function add(array $tagNames) : Collection
    {
        $tagNames = array_unique($tagNames);

        $tagToInsert = static::getTagToInsert($tagNames);

        static::addMany($tagToInsert);

        return static::whereIn("name", $tagNames)->pluck("id");
    }

    /**
     * Filter existing tags to add tags that haven't been created yet
     *
     * @param array $tagNames
     * @return array
     */
    private static function getTagToInsert(array $tagNames)
    {
        $existingTags = static::whereIn("name", $tagNames)->pluck("name");

        return array_filter($tagNames, function ($tagName) use ($existingTags) {
            return ! $existingTags->contains($tagName) && $tagName !== "";
        });
    }

    /**
     * Add many at once
     *
     * @param array $tags
     * @return void
     */
    private static function addMany (array $tags) : void
    {
        $tagsToInsert = array_map(function ($tagName) {
            return [
                "name" => $tagName,
                "slug" => Str::slug($tagName)
            ];
        }, $tags);

        static::insert($tagsToInsert);
    }
}

