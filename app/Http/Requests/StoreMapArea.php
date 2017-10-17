<?php

namespace Twinleaf\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMapArea extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'slug' => 'required|unique:map_areas,id,'.$this->area->id,
            'location' => 'required',
        ];
    }
}
