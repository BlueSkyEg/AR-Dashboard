<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DonationFormResource extends JsonResource
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
            'title' => $this->title,
            'fully_fund_level' => $this->fully_fund_level,
            'levels' => $this->levels,
            'recurring_periods' => [
                'day',
                'week',
                'month',
                'year'
            ]
        ];
    }
}
