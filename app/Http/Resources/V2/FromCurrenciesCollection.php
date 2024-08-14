<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;


class FromCurrenciesCollection extends ResourceCollection
{

    public function toArray($request)
    {
        return FromCurrenciesResource::collection($this->collection);
    }

}
