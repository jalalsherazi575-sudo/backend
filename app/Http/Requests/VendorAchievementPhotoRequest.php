<?php
namespace Laraspace\Http\Requests;

class VendorAchievementPhotoRequest extends Request
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
            'photo' => 'required',
			
            
        ];
		} else {
		return [
            'photo' => 'required',
			
        ];
		}
    }
}
