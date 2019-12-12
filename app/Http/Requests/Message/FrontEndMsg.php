<?php

<<<<<<< HEAD:app/Http/Requests/Message/FrontEndMsg.php
namespace App\Http\Message\Requests;
=======
namespace App\Http\Requests\ProjectAdmin;
>>>>>>> upstream/master:app/Http/Requests/ProjectAdmin/searchRequest.php

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

<<<<<<< HEAD:app/Http/Requests/Message/FrontEndMsg.php
class FrontEndMsg extends FormRequest
=======
class searchRequest extends FormRequest
>>>>>>> upstream/master:app/Http/Requests/ProjectAdmin/searchRequest.php
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
            'Content' => 'required',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        throw (new HttpResponseException(response()->fail(422, '参数错误！', $validator->errors()->all(), 422)));
    }
}