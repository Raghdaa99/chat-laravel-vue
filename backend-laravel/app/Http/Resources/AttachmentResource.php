<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AttachmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        return [
            'file_name' => $this['file_name'],
            'file_size' => $this['file_size'],
            'mimetype' => $this['mimetype'],
            'file_path' => asset('storage/' . $this['file_path']),
        ];
    }
}
