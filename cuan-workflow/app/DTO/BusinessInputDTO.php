<?php

namespace App\DTO;

use Illuminate\Http\Request;

class BusinessInputDTO
{
    public float $traffic;
    public float $conversion;
    public float $price;
    public float $cost;
    public float $fixed_cost;
    public float $target_revenue;

    public function __construct(
        float $traffic,
        float $conversion,
        float $price,
        float $cost,
        float $fixed_cost = 0,
        float $target_revenue = 0
    ) {
        $this->traffic = $traffic;
        $this->conversion = $conversion;
        $this->price = $price;
        $this->cost = $cost;
        $this->fixed_cost = $fixed_cost;
        $this->target_revenue = $target_revenue;
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            (float) $request->input('traffic', 0),
            (float) $request->input('conversion', 0),
            (float) $request->input('price', 0),
            (float) $request->input('cost', 0),
            (float) $request->input('fixed_cost', 0),
            (float) $request->input('target_revenue', 0)
        );
    }

    public function toArray(): array
    {
        return [
            'traffic' => $this->traffic,
            'conversion' => $this->conversion,
            'price' => $this->price,
            'cost' => $this->cost,
            'fixed_cost' => $this->fixed_cost,
            'target_revenue' => $this->target_revenue,
        ];
    }

    // Helper for sensitivity analysis (clone with modification)
    public function withTraffic(float $traffic): self
    {
        $clone = clone $this;
        $clone->traffic = $traffic;
        return $clone;
    }

    public function withConversion(float $conversion): self
    {
        $clone = clone $this;
        $clone->conversion = $conversion;
        return $clone;
    }

    public function withPrice(float $price): self
    {
        $clone = clone $this;
        $clone->price = $price;
        return $clone;
    }
}
