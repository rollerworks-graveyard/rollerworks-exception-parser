<?php

/*
 * This file is part of the Rollerworks ExceptionParser package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * This file is part of the Rollerworks ExceptionParser package.
 *
 * (c) 2014 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Component\ExceptionParser;

interface ExceptionParserInterface
{
    /**
     * Returns whether the processor accepts the exception.
     *
     * @param \Exception $exception
     *
     * @return bool
     */
    public function accepts(\Exception $exception);

    /**
     * Returns parameters parsed from the exception.
     *
     * @param \Exception $exception
     *
     * @return array
     */
    public function parseException(\Exception $exception);
}
