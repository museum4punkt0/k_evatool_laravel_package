<?php

namespace Twoavy\EvaluationTool\Rules;

use Illuminate\Contracts\Validation\Rule;
use Twoavy\EvaluationTool\Models\EvaluationToolAsset;

class IsMediaType implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($type)
    {
        $this->type = $type;
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
        if ($asset = EvaluationToolAsset::find($value)) {
            return strpos($asset->mime, $this->type) !== false;
        }
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'Must be of type ' . $this->type . ".";
    }
}
