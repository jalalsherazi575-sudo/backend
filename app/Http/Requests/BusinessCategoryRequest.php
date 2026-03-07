<?php
namespace Laraspace\Http\Requests;

class BusinessCategoryRequest extends Request
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
		if(!isset($_REQUEST['edit_id']))
		{
        return [
            'name' => 'required',
            'photo' => 'required',
        ];
		} else {
		return [
            'name' => 'required',
        ];
		}
    }
}
