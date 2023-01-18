<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostCollection;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagController extends Controller
{
    /**
     * Returns posts collection that have needed tag
     *
     * @param Tag $tag
     * @return void
     */
    public function getPosts(Tag $tag)
    {
        $posts = $tag->posts()->status()->latest()->paginate(10);
        return new PostCollection($posts);
    }

    /**
     * Display all tags
     *
     * @return void
     */
    public function index()
    {
        return TagResource::collection(Tag::withCount("posts")->get());
    }

    /**
     * Returns tag resource
     *
     * @param Tag $tag
     * @return void
     */
    public function show(Tag $tag)
    {
        return new TagResource($tag);
    }

    public function update(Request $request, Tag $tag)
    {
        $request->validate(["name"=>"required"]);
        if($request->name !== $tag->name) {
            $request->validate(["name" => "unique:tags"]);
        }

        $tag->update([
            "name" => $request->name,
            "title" => Str::slug($request->name)
        ]);

        return new TagResource($tag);
    }

    /**
     * Remove a tag resource.
     *
     * @param Tag $tag
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(Tag $tag)
    {
        $tag->delete();

        return response()->json([], 204);
    }
}
