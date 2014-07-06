<?php

/**
 * This file is part of the Rollerworks ExceptionParser package.
 *
 * (c) 2014 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Component\ExceptionParser;

/**
 * ExceptionParserManager parses exception and retrieves
 * exception parameters.
 */
class ExceptionParserManager
{
    /**
     * @var ExceptionParserInterface[]
     */
    protected $processors = array();

    /**
     * @var callable|string
     */
    protected $parameterTemplate;

    /**
     * @param string|callback|null $paramTemplate Template to use for variables
     *                                            or callback for transforming
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($paramTemplate = null)
    {
        if (is_callable($paramTemplate) || null === $paramTemplate) {
           // noop
        } elseif (!is_string($paramTemplate) || false === strpos($paramTemplate, '{var}')) {
            throw new \InvalidArgumentException(
                'Missing "{var}" placeholder in param-template or invalid callback.'
            );
        }

        $this->parameterTemplate = $paramTemplate;
    }

    /**
     * Adds processor for matching exception against.
     *
     * @param ExceptionParserInterface $processor
     *
     * @return ExceptionParserManager
     */
    public function addExceptionParser(ExceptionParserInterface $processor)
    {
        $this->processors[] = $processor;

        return $this;
    }

    /**
     * @return ExceptionParserInterface[]
     */
    public function getProcessors()
    {
        return $this->processors;
    }

    /**
     * @param \Exception $exception
     *
     * @return array Parameters parsed by exception.
     */
    public function processException(\Exception $exception)
    {
        foreach ($this->processors as $processor) {
            if ($processor->accepts($exception)) {
                return $this->processExceptionParams(
                    $processor->parseException($exception)
                );
            }
        }

        return array();
    }

    /**
     * Returns processed Exception parameters.
     *
     * @param array $params
     *
     * @return array
     */
    protected function processExceptionParams(array $params)
    {
        if (null === $this->parameterTemplate) {
            return $params;
        }

        $callback = is_callable($this->parameterTemplate) ? $this->parameterTemplate : null;
        $newParams = array();

        foreach ($params as $name => $value) {
            $name = $callback ? $callback($name) : str_replace('{var}', $name, $this->parameterTemplate);

            if (null === $name) {
                $newParams[] = $value;
            } else {
                $newParams[$name] = $value;
            }
        }

        return $newParams;
    }
}
