<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVideoRequest extends FormRequest
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
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration' => ['required', 'integer', 'min:1'],
            'is_free' => ['boolean'],
            'is_published' => ['boolean'],
            'position' => ['integer', 'min:0'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'metadata' => ['nullable', 'array'],
            'source_type' => ['required', 'string', Rule::in(['upload', 'youtube', 'vimeo', 'external_url', 's3', 'bunny'])],
            'source_url' => ['required_if:source_type,external_url,youtube,vimeo', 'nullable', 'url'],
            'video_file' => ['required_if:source_type,upload', 'file', 'mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/x-flv,video/webm', 'max:102400'], // Max 100MB
            's3_path' => ['required_if:source_type,s3', 'string'],
            'bunny_video_id' => ['required_if:source_type,bunny', 'string'],
        ];

        // Add specific validation rules for YouTube and Vimeo URLs
        if ($this->input('source_type') === 'youtube') {
            $rules['source_url'][] = 'regex:/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.?be)\/.+$/';
        } elseif ($this->input('source_type') === 'vimeo') {
            $rules['source_url'][] = 'regex:/^(https?:\/\/)?(www\.|player\.)?vimeo\.com\/.+$/';
        }

        return $rules;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // If duration is in HH:MM:SS format, convert to seconds
        if ($this->has('duration') && is_string($this->duration) && preg_match('/^\d+:\d{2}(:\d{2})?$/', $this->duration)) {
            $parts = array_reverse(explode(':', $this->duration));
            $seconds = 0;
            
            if (count($parts) >= 1) $seconds += (int) $parts[0]; // seconds
            if (count($parts) >= 2) $seconds += (int) $parts[1] * 60; // minutes
            if (count($parts) >= 3) $seconds += (int) $parts[2] * 3600; // hours
            
            $this->merge(['duration' => $seconds]);
        }
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'source_url' => 'video URL',
            'video_file' => 'video file',
            's3_path' => 'S3 path',
            'bunny_video_id' => 'Bunny Video ID',
        ];
    }
}
