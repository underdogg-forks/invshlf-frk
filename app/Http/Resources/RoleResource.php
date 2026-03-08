<?php

namespace App\Http\Resources;

use App\Models\CompanySetting;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'scope' => $this->team_id,
            'formatted_created_at' => $this->getFormattedAt(),
            'abilities' => $this->whenLoaded('permissions', function () {
                return $this->permissions->map(fn ($permission) => ['ability' => $permission->name]);
            }),
        ];
    }

    public function getFormattedAt()
    {
        $dateFormat = CompanySetting::getSetting('carbon_date_format', $this->team_id);

        return Carbon::parse($this->created_at)->translatedFormat($dateFormat);
    }
}
