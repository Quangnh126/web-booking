<?php


namespace App\Http\Requests\Web;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class AuthRequest extends FormRequest
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
        $action = $this->segments()[1];

        switch ($action) {
            case 'register':
                return [
                    'email' => 'required|regex:/^([a-z0-9\+_.\-]{2,65}+)(\.[a-z0-9\+_\-]{2,65}+)*@([a-z0-9\-]{2,65}+\.)+[a-z]{2,6}$/ix|unique:users,email',
                    'phone_number' => 'required|min:10|max:15',
                    'display_name' => 'required|min:1|max:255|string',
                    'password' => 'required|regex:/^(?=.*[A-Za-z0-9!@#$%^&*()\-_=+{};:,<.>])(?!\S*?\s)\S{6,65}$/u',
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
                'password.regex' => 'Mật khẩu phải dài từ 6 đến 65 kí tự',
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
                'data' => []
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
    }
}
