<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'stname' => $this->stname,
            'stlocation' => $this->stlocation,
            'stcontact' => $this->stcontact,
            'store_img' => $this->store_img,
            'is_favorite' => $this->is_favorite,
        ];
    }
}
