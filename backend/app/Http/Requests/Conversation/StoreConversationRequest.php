<?php
namespace App\Http\Requests\Conversation;

use Illuminate\Foundation\Http\FormRequest;

class StoreConversationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'receiver_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'receiver_id.exists' => 'Cet utilisateur n\'existe pas.',
        ];
    }
}