<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class SafeFileName implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    protected array $allowedExtensions;
    protected string $fileName;
    protected string $filePath;

    public function __construct(array $allowedExtensions = ['pdf', 'jpg', 'png', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'rar' ])
    {
        $this->allowedExtensions = $allowedExtensions;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
          // Avoid dangerous path patterns
          foreach (['..', './', '\\', '//'] as $dangerousPattern) {
              if (str_contains($value, $dangerousPattern)) {
                  return false;
              }
          }   
               
        // Build regex dynamically based on allowed extensions
        $ext = implode('|', array_map('preg_quote', $this->allowedExtensions));
        // Allow folder/filename.ext or just filename.ext
        $pattern = '/^([a-z0-9_\-]+\/)?[a-zA-Z0-9_\-]+\.(?:' . $ext . ')$/i';

        return preg_match($pattern, $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.file_name');
    }
}
