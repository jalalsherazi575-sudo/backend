<?php

namespace Laraspace\Http\Requests;
use Illuminate\Validation\Rule;

class TopicRequest extends Request
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
     * @return array
     */
    public function rules()
    {
        $id = $this->route('id'); // Retrieve the ID of the current topic being edited (if any)
        return [
            'category' => 'required',
            'subject' => 'required',
            'topicName' => [
                'required',
                Rule::unique('topics')
                    ->where(function ($query) {
                        return $query->where('subjectId', $this->input('subject'));
                    })
                    ->ignore($id),
            ],
            'status' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'category.required' => 'Please select the category.',
            'subject.required' => 'Please select the subject.',
            'topicName.required' => 'Please enter the topic name.',
            'topicName.unique' => 'The combination of category ID, subject ID, and topic name must be unique.',
        ];
    }
}
