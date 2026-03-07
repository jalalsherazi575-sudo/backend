<?php

namespace Laraspace\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Laraspace\Questions;
use Laraspace\TopicQueRel;
use Laraspace\QuestionOption;
class QuestionsImport implements ToCollection,SkipsOnError,WithValidation,SkipsOnFailure,WithHeadingRow
{
      use Importable,SkipsErrors,SkipsFailures;

    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Create Question
            if(!empty($row['question'])){
               $question = Questions::create([
                    'question' => $row['question'],
                    'description' => $row['description'],
                    'questionType' => '1',
                    'isActive' => 1
                ]);
                // Create TopicQueRel
                $topicIds = explode(',', $row['topicid']);
               foreach ($topicIds as $topicId) {
                    TopicQueRel::create([
                        'topicId' => $topicId,
                        'questionId' => $question->questionId
                    ]);

                }

                // Create Options
                for ($i = 1; $i <= 5; $i++) {
                    if (!empty($row['option'.$i])) {

                        QuestionOption::create([
                            'questionId' => $question->questionId,
                            'questionImageText' => $row['option'.$i],
                            'isCorrectAnswer' => $row['is_correct_answer'.$i] == 1
                        ]);
                    }
                }
            }
        }
    }

    public function rules(): array
    {
        return [
            //'question' => 'required|max:1000',
            'option1' => 'nullable|max:1000',
            'option2' => 'nullable|max:1000',
            'option3' => 'nullable|max:1000',
            'option4' => 'nullable|max:1000',
            'option5' => 'nullable|max:1000',
        ];
    }
       public function customValidationMessages()
    {
        return [
            'question.required' => 'The question field is required.',
            'question.max' => 'The question may not be greater than 1000 characters.',
            'option1.max' => 'Option 1 may not be greater than 1000 characters.',
            'option2.max' => 'Option 2 may not be greater than 1000 characters.',
            'option3.max' => 'Option 3 may not be greater than 1000 characters.',
            'option4.max' => 'Option 4 may not be greater than 1000 characters.',
            'option5.max' => 'Option 5 may not be greater than 1000 characters.',
        ];
    }
}
