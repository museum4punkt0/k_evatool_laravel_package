<?php

namespace Twoavy\EvaluationTool\Rules;

use Illuminate\Contracts\Validation\Rule;

class DuplicatesInArray implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $uniqueValues = [];
        foreach ($value as $val) {
            if (in_array($val, $uniqueValues)) {
                return false;
            }
            $uniqueValues[] = $val;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'There are duplicate elements.';
    }
}
