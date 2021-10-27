<?php

namespace Twoavy\EvaluationTool\Rules;

use Illuminate\Contracts\Validation\Rule;
use Kozz\Components\Emoji\EmojiParser;

class Emoji implements Rule
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
        // Todo: Check if only one emojy is present
        $parser  = new EmojiParser();
        $matches = $parser->match($value);
        return $matches > 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'Must be an emoji 😊';
    }
}
