<?php

namespace Twinleaf\Rules;

use Illuminate\Contracts\Validation\Rule;

class HexColour implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return !!preg_match('/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/', $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be a valid hex colour.';
    }
}
