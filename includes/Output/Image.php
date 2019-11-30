<?php

/**
 * Output processor for images.
 *
 * Accepts filepath, or remote URL.
 * This will return the actual image, not the URL.
 */

namespace Gaterdata\Output;

use Gaterdata\Core;

class Image extends Output
{
    /**
     * {@inheritDoc}
     */
    protected $details = [
        'name' => 'Image',
        'machineName' => 'image',
        // phpcs:ignore
        'description' => 'Output in image format. The data fed into the output can be a URL (must start with http) or an input filename.',
        'menu' => 'Output',
        'input' => [
            'destination' => [
                'description' => 'Destination URLs for the output.',
                'cardinality' => [1, '*'],
                'literalAllowed' => true,
                'limitFunctions' => [],
                'limitTypes' => ['string'],
                'limitValues' => [],
                'default' => '',
            ],
            'method' => [
                'description' => 'HTTP delivery method when sending output. Only used in the output section.',
                'cardinality' => [0, '1'],
                'literalAllowed' => true,
                'limitFunctions' => [],
                'limitTypes' => ['string'],
                'limitValues' => ['get', 'post'],
                'default' => '',
            ],
            'options' => [
                // phpcs:ignore
                'description' => 'Extra Curl options to be applied when sent to the destination (e.g. cursor: -1, screen_name: foobarapi, skip_status: true, etc).',
                'cardinality' => [0, '*'],
                'literalAllowed' => true,
                'limitFunctions' => ['field'],
                'limitTypes' => ['string'],
                'limitValues' => [],
                'default' => '',
            ],
        ],
    ];

    /**
     * {@inheritDoc}
     */
    protected function fromXml(&$data)
    {
        return 'data is not an image';
    }

    /**
     * {@inheritDoc}
     */
    protected function fromFloat(&$data)
    {
        return 'data is not an image';
    }

    /**
     * {@inheritDoc}
     */
    protected function fromBoolean(&$data)
    {
        return 'data is not an image';
    }

    protected function fromInteger(&$data)
    {
        return 'data is not an image';
    }

    /**
     * {@inheritDoc}
     */
    protected function fromJson(&$data)
    {
        return 'data is not an image';
    }

    /**
     * {@inheritDoc}
     */
    protected function fromHtml(&$data)
    {
        return 'data is not an image';
    }

    /**
     * {@inheritDoc}
     */
    protected function fromText(&$data)
    {
        return $data;
    }

    /**
     * {@inheritDoc}
     */
    protected function fromArray(&$data)
    {
        return 'data is not an image';
    }

    /**
     * {@inheritDoc}
     */
    protected function fromImage(&$data)
    {
        return $data;
    }
}
