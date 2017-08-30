<?php

namespace SmsBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class IsValidUKMobile extends Constraint
{
    public $message = 'The phone number "{{ string }}" is not a valid UK mobile number.';
}