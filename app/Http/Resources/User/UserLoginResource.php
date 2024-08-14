<?php

namespace App\Http\Resources\User;

use App\Models\Country;
use Illuminate\Http\Resources\Json\JsonResource;

class UserLoginResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $defaultCountry = Country::where('is_default', 'yes')->value('short_name');
        return [
            'user_id'        => $this->id,
            'first_name'     => $this->first_name,
            'last_name'      => $this->last_name,
            'full_name'      => $this->full_name,
            'email'          => $this->email,
            'formattedPhone' => $this->formattedPhone,
            'picture'        => image($this->picture, 'profile'),
            'defaultCountry' => strtolower($defaultCountry),
            'token'          => $this->createToken($this->full_name . ' ' . $this->formattedPhone)->accessToken,
            'userStatus'     => $this->status,
            'address_verified'  => $this->address_verified == true,
            'identity_verified' => $this->identity_verified == true,
            'access_web'        => $this->access_web == true,
            'biometric_login'   => $this->biometric_login == true
          ];
    }
}
