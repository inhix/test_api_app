<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "title" => $this->title,
            "slug"  => $this->slug,
            "content" => $this->content,
            "created_at" => $this->created_at->diffForHumans(),
            "status" => !! $this->online,
            "authot" => $this->author,
            "photo_path" => $this->cover_path,
            "tags" => TagResource::collection($this->tags()->get())
        ];
    }
}
