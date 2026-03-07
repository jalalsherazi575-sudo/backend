<?php
namespace Laraspace\Http\Requests;

class CmsPagesRequest extends Request
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
    // If $id represents the ID of the current record being updated
    // You should replace 'tableName' with the actual name of your table
    $id = $this->route('id');

    return [
        'name' => 'required|unique:tblcms,name,' . $id,
    ];
}

public function messages()
{
    return [
        'name.required' => 'Please enter the name.',
        'name.unique' => 'The name has already been taken.',
    ];
}

}
