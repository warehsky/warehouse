<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class EmailRequest extends FormRequest
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
            'subject' => 'required',
            'text' => 'required'
        ];
    }

    public function messages(){
        return [
            'subject.required' => 'Поле "Тема email" является обязательным',
            'text.required' => 'Поле "Текст email" является обязательным'
        ];
    }




}
