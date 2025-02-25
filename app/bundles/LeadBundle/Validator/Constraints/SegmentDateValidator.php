<?php

declare(strict_types=1);

namespace Mautic\LeadBundle\Validator\Constraints;

use Mautic\LeadBundle\Segment\ContactSegmentFilterFactory;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\Translation\TranslatorInterface;

final class SegmentDateValidator extends ConstraintValidator
{
    public function __construct(private ContactSegmentFilterFactory $contactSegmentFilterFactory, private TranslatorInterface $translator)
    {
    }

    /**
     * @param array<mixed> $filters
     */
    public function validate($filters, Constraint $constraint): void
    {
        foreach ($filters as $filter) {
            if (isset($filter['type']) && in_array($filter['type'], ['date', 'datetime'])) {
                $segmentFilter  = $this->contactSegmentFilterFactory->factorSegmentFilter($filter);
                $parameterValue = $segmentFilter->getParameterValue();
                if (is_array($parameterValue)) {
                    continue;
                }

                $dateString = str_replace('%', '', $parameterValue);

                $format = 'Y-m-d';

                $dateTime = \DateTime::createFromFormat($format, $dateString);
                if (false === $dateTime) {
                    $this->context->addViolation($this->translator->trans('mautic.lead.segment.date_invalid', ['%value%' => $parameterValue], 'validators'));

                    return;
                }
            }
        }
    }
}
