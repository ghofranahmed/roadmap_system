<?php 
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitChallengeAttemptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'code' => ['required','string','min:1'],
        ];
    }
}
