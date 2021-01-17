<?php

namespace App\Validations;

interface ValidationInterface
{
    public function validate(object $request, string $type);
}
