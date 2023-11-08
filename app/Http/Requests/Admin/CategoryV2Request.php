<?php


namespace App\Http\Requests\Admin;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CategoryV2Request extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        $action = $this->segments()[3];

        switch ($action) {
            case 'create':
                return [
                    'name' => 'required|string|max:65',
                    'number' => 'required',
                    'description' => 'string',
                ];
                break;
            case 'update':
                return [
                    'name' => 'required|string|max:65',
                    'number' => 'required',
                    'description' => 'string'
                ];
                break;
            case 'delete-staff':
                return [
                    'id' => 'required|integer',
                ];
                break;

            default:
                return [];
        }
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        $lang = ($this->hasHeader('language')) ? $this->header('language') : 'vi';

        if ($lang == 'vi') {
            return [
                'name.required' => 'Tên danh mục là trường bắt buộc',
                'name.max' => 'Tên danh mục không quá 65 kí tự',
                'number.required' => 'Số lượng người là trường bắt buộc',
            ];
        }
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();
        throw new HttpResponseException(response()->json(
            [
                'error' => $errors,
                'status_code' => 422,
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
    }
}
