<?php

namespace Twoavy\EvaluationTool\Rules;

use Illuminate\Contracts\Validation\Rule;

class Timecode implements Rule
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
        return preg_match('/(^(?:(?:[0-1][0-9]|[0-2][0-3]):)(?:[0-5][0-9]:){2}(?:[0-2][0-9])$)+/', $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'Must be in timecode format (i.e. 00:00:00:00).';
    }
}
