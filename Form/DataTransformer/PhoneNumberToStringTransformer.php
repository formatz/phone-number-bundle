<?php

/*
 * This file is part of the Symfony2 PhoneNumberBundle.
 *
 * (c) University of Cambridge
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Misd\PhoneNumberBundle\Form\DataTransformer;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Phone number to string transformer.
 *
 * @author Chris Wilkinson <chris.wilkinson@admin.cam.ac.uk>
 */
class PhoneNumberToStringTransformer implements DataTransformerInterface
{
    /**
     * Default region code.
     *
     * @var string
     */
    private $defaultRegion;

    /**
     * Display format.
     *
     * @var int
     */
    private $format;

    /**
     * Constructor.
     *
     * @param string $defaultRegion Default region code.
     * @param int    $format        Display format.
     */
    public function __construct(
        $defaultRegion = PhoneNumberUtil::UNKNOWN_REGION,
        $format = PhoneNumberFormat::INTERNATIONAL
    ) {
        $this->defaultRegion = $defaultRegion;
        $this->format = $format;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($phoneNumber)
    {
        $util = PhoneNumberUtil::getInstance();

        if (null === $phoneNumber) {
            return '';
        } elseif (false === $phoneNumber instanceof PhoneNumber) {
            if(is_string($phoneNumber)) {
                try {
                    $phoneNumber = $util->parse($phoneNumber, PhoneNumberUtil::UNKNOWN_REGION);
                } catch (NumberParseException $e) {
                    throw new TransformationFailedException($e->getMessage(), $e->getCode(), $e);
                }
            }
            else {
                throw new TransformationFailedException('Expected a \libphonenumber\PhoneNumber.');
            }
        }

        if (PhoneNumberFormat::NATIONAL === $this->format) {
            return $util->formatOutOfCountryCallingNumber($phoneNumber, $this->defaultRegion);
        }

        return $util->format($phoneNumber, $this->format);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($string)
    {
        if (!$string) {
            return null;
        }

        $util = PhoneNumberUtil::getInstance();

        try {
            return $util->format($util->parse($string, $this->defaultRegion), PhoneNumberFormat::E164);
        } catch (NumberParseException $e) {
            throw new TransformationFailedException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
