<?php

namespace App\Http\Resources;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResourceIndex extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'imageUrl' => $this->getImageUrl(),
            'title' => $this->when($this->title,$this->title),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'slug' => $this->when($this->slug,$this->slug),
            'summary' => $this->when($this->summary,$this->summary),
            'created_at_formated' => $this->when($this->created_at, function(){
                return $this->created_at->diffForHumans();
            }),
            'created_date' => $this->when($this->created_at,function() {return $this->created_at->format('M d, Y');}),
            'updated_date' => $this->when($this->updated_at,function() {return $this->updated_at->format('M d, Y');})
           ];
    }
}
