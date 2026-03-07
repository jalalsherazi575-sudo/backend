<?php
namespace Laraspace\Http\Requests;

class QuestionsRequest extends Request
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
		return [
            'topicId' => 'required',
            'questionType' => 'required',
            'question' => 'required',
            //'description' => 'required'
        ];
    }
    public function messages()
    {
        return [
            'topicId.required' => 'Please select the topics.',
            'questionType.required' => 'Please enter the question Type.',
            'question.required' => 'Please enter the question.',
            'description.required' => 'Please enter the description.'
        ];
    }
}
