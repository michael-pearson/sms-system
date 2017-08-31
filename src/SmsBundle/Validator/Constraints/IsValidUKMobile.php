<?php

namespace SmsBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class IsValidUKMobile extends Constraint
{
    /**
     * The error message displayed when validation fails.
     */
    public $message = 'The phone number "{{ string }}" is not a valid UK mobile number.';
}