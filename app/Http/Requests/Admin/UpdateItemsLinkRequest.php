<?php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class UpdateItemsLinkRequest extends FormRequest
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
            'title' => 'required',
            'parentId' => 'nullable|exists:itemGroups,id',
            //'imgIcon' => 'nullable|image|mimes:svg|max:1014',
            'imgSmall' => 'nullable|image|mimes:png|max:1014',
            'imgBig' => 'nullable|image|mimes:png|max:1014'
        ];
    }
}
