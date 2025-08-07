<?php

namespace App\Http\Requests\Admin;

use App\Models\Video;
use Illuminate\Validation\Rule;

class UpdateVideoRequest extends StoreVideoRequest
{
    /**
     * The video instance.
     *
     * @var \App\Models\Video
     */
    protected $video;

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();
        $this->video = $this->route('video');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = parent::rules();
        
        // Make video file optional for updates unless source type is being changed to upload
        if ($this->input('source_type') !== 'upload' || $this->video->source_type === 'upload') {
            unset($rules['video_file']);
        }
        
        // If source_type isn't changing, remove required validation for source-specific fields
        if ($this->input('source_type') === $this->video->source_type) {
            unset($rules['source_url']);
            unset($rules['s3_path']);
            unset($rules['bunny_video_id']);
        }
        
        return $rules;
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->video);
    }
}
