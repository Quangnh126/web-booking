<?php


namespace App\Http\Requests\Web;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class OrderRequest extends FormRequest
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
        $action = $this->segments()[2];
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
                'password.required' => 'Mật khẩu là trường bắt buộc',
                'email.required' => 'Email là trường bắt buộc',
                'email.unique' => 'Email đã tồn tại',
                'email.regex' => 'Email sai định dạng',
                'phone_number.max' => 'Số điện thoại không đúng định dạng',
                'phone_number.min' => 'Số điện thoại không đúng định dạng',
                'phone_number.required' => 'Số điện thoại là trường bắt buộc',
                'user_id.required' => 'Mã người dùng là trường bắt buộc',
                'user_id.integer' => 'Mã người dùng là số nguyên',
                'display_name.min' => 'Tên người dùng sai định dạng',
                'display_name.max' => 'Tên người dùng sai định dạng',
                'display_name.required' => 'Tên người dùng là trường bắt buộc',
                'role_id.in' => 'Chỉ chấp nhận giá trị 1, 3',
                'password.regex' => 'Mật khẩu phải dài từ 6 đến 65 kí tự',
            ];
        } else {
            return [

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
