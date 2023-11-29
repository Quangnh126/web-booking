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

        switch ($action){
            case 'booking-room':
                return [
                    'start_date' => 'required',
                    'end_date' => 'required|after:start_date',
                ];
                break;

            case 'booking-tour':
                return [
                ];
                break;

            case 'list-order':
                return [
                    'status.*' => 'in:pending,access,ending,cancel,pending_cancel|string',
                ];
                break;
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
                'end_date.after' => 'Ngày kết thúc booking phải lớn hơn ngày bắt đầu !!',
                'status.in' => 'Status của đơn đặt chỉ chấp nhận các giá trị: pending, access, ending, cancel',
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
