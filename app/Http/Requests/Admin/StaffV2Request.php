<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class StaffV2Request extends FormRequest
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

            case 'index':
                return [
                    'role_id' => [function ($attribute, $value, $fail) {
                        if (array_diff($value, [1, 3])) {
                            $fail('The '.$attribute.' must be either 1 or 3.');
                        }
                    }, 'nullable'],
                ];
                break;
            case 'show-staff':
                return [
                ];
                break;
            case 'create':
                return [
                    'email' => 'required|regex:/^([a-z0-9\+_.\-]{2,65}+)(\.[a-z0-9\+_\-]{2,65}+)*@([a-z0-9\-]{2,65}+\.)+[a-z]{2,6}$/ix|unique:users,email',
                    'role_id' => 'in:1,3|integer|required',
                    'phone_number' => 'required|min:10|max:15',
                    'display_name' => 'required|min:1|max:255|string',
                    'password' => 'required|regex:/^(?=.*[A-Za-z0-9!@#$%^&*()\-_=+{};:,<.>])(?!\S*?\s)\S{6,65}$/u',
                ];
                break;
            case 'update':
                return [
                    'role_id' => 'in:1,3|integer|required',
                    'display_name' => 'required|min:1|max:255|string',
                    'phone_number' => 'required|min:10|max:15',
                    'password' => 'nullable|regex:/^(?=.*[A-Za-z0-9!@#$%^&*()\-_=+{};:,<.>])(?!\S*?\s)\S{6,65}$/u',
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
                'password.required' => 'Password is a required field',
                'email.required' => 'Email is a required field',
                'email.unique' => 'Email already exists',
                'email.regex' => 'Invalid email format',
                'phone_number.max' => 'Invalid phone number',
                'phone_number.min' => 'Invalid phone number',
                'phone_number.required' => 'Phone number is required field',
                'user_id.required' => 'User ID is required field',
                'user_id.integer' => 'User ID is an integer',
                'display_name.max' => 'Invalid fullname',
                'display_name.min' => 'Invalid fullname',
                'display_name.required' => 'Fullname is required field',
                'role_id.in' => 'Only accept value 1, 3',
                'password.regex' => 'Password must be between 6 and 65 characters',

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
