<?php

namespace SmsBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsValidUKMobileValidator extends ConstraintValidator
{
    /**
     * Validates a pass parameter to see if it is a valid UK
     * mobile phone number.
     *
     * @param mixed $_value
     * @param Constraint $_constraint
     * @return void
     */
    public function validate($_value, Constraint $_constraint)
    {
        // If it is not a valid UK mobile phone number.
        if(!preg_match('/^(\+447\d{3}|\(?07\d{3}\)?)\d{3}\d{3}$/', $_value, $matches))
        {
            // Add a validation violation.
            $this->context->buildViolation($_constraint->message)
                ->setParameter('{{ string }}', $_value)
                ->addViolation();
        }
    }
}