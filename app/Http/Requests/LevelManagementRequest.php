<?php
namespace Laraspace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LevelManagementRequest extends FormRequest
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
         $id = $this->route('id'); // Retrieve the ID of the current record being edited (if any)

        if ($this->isMethod('post')) {
            return [
                'levelName' => 'required|unique:tbllevelmanagement,levelName',
                'sortOrder' => 'required|unique:tbllevelmanagement,sortOrder',
                'catImage'  => 'required|image|mimes:jpeg,png,jpg',
            ];
        } elseif ($this->isMethod('put')) {
            return [
                'levelName' => [
                    'required',
                   Rule::unique('tbllevelmanagement')->ignore($id, 'levelId'),
                ],
                'sortOrder' => [
                    'required',
                   Rule::unique('tbllevelmanagement')->ignore($id, 'levelId'),
                ],
                
            ];
        }
    }


    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'levelName.required'    => 'Please enter the category name.',
            'levelName.unique'      => 'The category name has already been taken.',
            'sortOrder.required'    => 'Please enter the display rank.',
            'sortOrder.unique'      => 'The display rank has already been taken.',
            'catImage.required'     => 'Please upload the category image.',
            'catImage.*.mimes'      => 'Only jpeg,png and jpg images are allowed.',
        ];
    }
}
