<?php
namespace Laraspace\Http\Requests;
use Illuminate\Validation\Rule;

class BannerRequest extends Request
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
        $rules = [
            'bannerTitle' => 'required',
            'bannerUrl' => 'required|url|max:255',
            'startDate' => [
                'required',
                'date',
            ],
            'endDate' => 'required|date|after:startDate',
        ];

        // If it's not a new banner (update), apply the after_or_equal:today rule to startDate
            if ($this->isMethod('post')) {

                $rules['startDate'][] = 'after_or_equal:today';
            }

            // If it's a new banner (creation), enforce image upload requirement
            if ($this->isMethod('post')) {
                $rules['bannerImage'] = 'required|image|mimes:jpeg,png,jpg';
            }

            return $rules;
    }


    public function messages()
    {
        return [
            'bannerTitle.required' => 'Please enter the title.',
            'bannerUrl.required' => 'Please enter the URL.',
            'startDate.required' => 'Please select the start date.',
            'endDate.required' => 'Please select the end date.',
            'startDate.after_or_equal' => 'The start date must be today or in the future.',
            'endDate.after' => 'The end date must be after the start date.',
        ];
    }
}
