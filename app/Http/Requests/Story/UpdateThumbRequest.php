<?php

namespace App\Http\Requests\Story;

use Illuminate\Foundation\Http\FormRequest;

class UpdateThumbRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => 'required',
            'file' => 'required|image|max:1999'
        ];
    }
}
