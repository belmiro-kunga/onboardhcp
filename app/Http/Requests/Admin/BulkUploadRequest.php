<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Video::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'videos' => ['required', 'array', 'min:1', 'max:20'], // Limit to 20 videos per batch
            'videos.*.file' => ['required', 'file', 'mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/x-flv,video/webm', 'max:102400'], // Max 100MB per file
            'videos.*.title' => ['required', 'string', 'max:255'],
            'videos.*.description' => ['nullable', 'string'],
            'videos.*.is_free' => ['boolean'],
            'videos.*.is_published' => ['boolean'],
            'videos.*.position' => ['nullable', 'integer', 'min:0'],
            'videos.*.metadata' => ['nullable', 'array'],
            'course_id' => ['required', 'exists:courses,id'],
            'notify_on_complete' => ['boolean'],
            'email_notification' => ['nullable', 'email', 'required_if:notify_on_complete,true'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'videos.*.file' => 'video file',
            'videos.*.title' => 'video title',
            'course_id' => 'course',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure all array fields are properly formatted
        $videos = $this->input('videos', []);
        
        foreach ($videos as $key => $video) {
            if (is_string($video['metadata'] ?? null)) {
                $videos[$key]['metadata'] = json_decode($video['metadata'], true) ?? [];
            }
        }
        
        $this->merge([
            'videos' => $videos,
            'notify_on_complete' => (bool) $this->input('notify_on_complete', false),
        ]);
    }
}
