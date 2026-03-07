<?php
namespace Laraspace\Http\Requests;
use Illuminate\Validation\Rule;
class SubjectRequest extends Request
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
        $id = $this->route('id');
		return [
            'category' => 'required',
            'subjectName' => [
                'required',
                Rule::unique('subject')->where(function ($query) {
                    return $query->where('categoryId', $this->input('category'));
                })->ignore($id),
            ],
            'subImage' => $id ? 'nullable|image' : 'required|image', // Required and image rule
            'status' => 'required',
        ];
    }
}
