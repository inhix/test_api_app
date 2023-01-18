<?php

namespace App\Http\Controllers;

use App\Filters\PostsFilter;
use App\Http\Requests\PostRequest;
use App\Http\Resources\PostCollection;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    protected $filter;

    public function __constructor(PostsFilter $filter)
    {
        $this->filter = $filter;
    }

    /**
     * Fetch filtered posts that have posted status.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $filteredPosts = $this->filter->apply(
            request()->all(),
            Post::status()->latest()
        );

        $posts = $filteredPosts->paginate(10);

        return new PostCollection($posts);
    }

    /**
     * TODO all posts
     */



    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\PostRequest $request
     * @return \App\Http\Resources\PostResource
     */
    public function store(PostRequest $request)
    {
        $data = $request->data();
        $data["photo"] = $this->uploadPhoto($request->file("photo"));

        $post = Post::create($data);

        $tagsId = Tag::add(explode(",", $request->tags));
        $post->tags()->attach($tagsId);

        return new PostResource($post);
    }

    /**
     * Upload photo to storage and returns it's file path
     *
     * @param UploadedFile $file
     * @return string
     */
    private function uploadPhoto(UploadedFile $file) : string
    {
        $filename = time() . "." . $file->getClientOriginalExtension();

        $file->storeAs("public/photos", $filename);

        return asset("storage/photos/". $filename);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
        $post->load(["author", "tags"]);

        return new PostResource($post);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Post $post)
    {
        $data = $request->data();

        $data = $request->data();

        if($request->file("cover")) {
            Storage::delete("public/covers/" . $post->cover);

            $data["cover_path"] = $this->uploadCover($request->file("cover"));
        }

        $post->update($data);

        $tagsId = Tag::add(explode(",", $request->tags));
        $post->tags()->sync($tagsId);

        return new PostResource($post);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Post $post)
    {
        $post->delete();

        Storage::delete("public/covers/{$post->cover}");

        return response()->json([], 204);
    }
}
