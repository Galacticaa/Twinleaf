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
        $rules = [
            'name' => 'required',
            'slug' => 'required|unique:map_areas',
            'location' => 'required',
        ];

        if ($this->area) {
            $rules['slug'] = 'required|unique:map_areas,id,'.$this->area->id;
            $rules['accounts_target'] = 'required|integer|min:0';
            $rules['proxy_target'] = 'required|integer|min:0';
            $rules['db_threads'] = 'nullable|integer|max:255';
        }

        return $rules;
    }
}
