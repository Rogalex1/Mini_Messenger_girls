<?php
namespace App\Http\Requests\Message;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'message'        => ['required_without:file', 'nullable', 'string', 'max:5000'],
            'type'           => ['required', 'in:text,image,audio,video,file,gif'],
            'is_single_view' => ['boolean'],
            'reply_to_id'    => ['nullable', 'integer', 'exists:messages,id'],
        ];
    }
}