<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class GalleryRequest extends FormRequest
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
            'travel_packages_id'    =>  'required|integer|exists:travel_packages,id', // maksud exist adalah bahwa field travel_packages_id harus ada dengan field id di table _travel_packages
            'image'                 =>  'required|image',
        ];
    }
}
