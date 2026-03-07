<?php

namespace Laraspace\Http\Requests;
use Illuminate\Validation\Rule;

class PlanPackageRequest extends Request
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

        $id = $this->route('id'); // Retrieve the ID of the current plan package being edited (if any)
     
        return [
            'subId' => 'required|exists:subject,id',
            'packageName' => [
                'required',
                Rule::unique('tblplanpackage')->where(function ($query) {
                    return $query->where('subjectId', $this->input('subId'));
                })->ignore($id,'packageId'),
            ],
            'packagePrice' => 'required|numeric|min:0',
            //'packagePeriodInMonth' => 'required',
            'isActive' => 'required',
            // Add other validation rules as needed
        ];
    }
    public function messages()
    {
        return [
            /*'subId.required' => 'The subject ID field is required.',
            'subId.exists' => 'The selected subject ID does not exist.',*/
            'packageName.required' => 'Please enter the package name.',
            'packageName.unique' => 'The package name has already been taken for this subject.',
            'packagePrice.required' => 'Please enter the package price.',
            'packagePrice.numeric' => 'The package price must be a number.',
            'packagePrice.min' => 'The package price must be at least :min.',
            'isActive.required' => 'Please select the status',
            // Add other custom error messages as needed
        ];
    }
}
