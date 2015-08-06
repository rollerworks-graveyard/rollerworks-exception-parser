<?php

/*
 * This file is part of the Rollerworks ExceptionParser package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace spec\Rollerworks\Component\ExceptionParser;

use PhpSpec\ObjectBehavior;
use Rollerworks\Component\ExceptionParser\ExceptionParserInterface;

class ExceptionParserManagerSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('Rollerworks\Component\ExceptionParser\ExceptionParserManager');
    }

    public function it_has_no_processor_by_default()
    {
        $this->getProcessors()->shouldReturn(array());
    }

    public function it_allows_adding_processors(
        ExceptionParserInterface $exceptionParser,
            ExceptionParserInterface $exceptionParser2
    ) {
        $this->addExceptionParser($exceptionParser);
        $this->addExceptionParser($exceptionParser2);

        $this->getProcessors()->shouldReturn(
            array(
                $exceptionParser,
                $exceptionParser2
            )
        );
    }

    public function it_parses_an_accepted_exception(ExceptionParserInterface $exceptionParser)
    {
        $exception1 = new \Exception('Oh no');
        $exception2 = new \Exception('Whoops');

        $exceptionParser->accepts($exception1)->willReturn(true);
        $exceptionParser->parseException($exception1)->willReturn(array('foo' => 'bar'));

        $exceptionParser->accepts($exception2)->willReturn(false);
        $exceptionParser->parseException($exception2)->shouldNotBeCalled();

        $this->addExceptionParser($exceptionParser);

        $this->processException($exception1)->shouldReturn(array('foo' => 'bar'));
        $this->processException($exception2)->shouldReturn(array());
    }

    public function it_changes_exception_params_using_a_pattern(ExceptionParserInterface $exceptionParser)
    {
        $this->beConstructedWith('{{ {var} }}');

        $exception1 = new \Exception('Oh no');

        $exceptionParser->accepts($exception1)->willReturn(true);
        $exceptionParser->parseException($exception1)->willReturn(array('foo' => 'bar'));

        $this->addExceptionParser($exceptionParser);

        $this->processException($exception1)->shouldReturn(array('{{ foo }}' => 'bar'));
    }

    public function it_changes_exception_params_using_a_callback(ExceptionParserInterface $exceptionParser)
    {
        $this->beConstructedWith(
             function ($name) {
                 return '{{ '.$name.' }}';
             }
        );

        $exception1 = new \Exception('Oh no');

        $exceptionParser->accepts($exception1)->willReturn(true);
        $exceptionParser->parseException($exception1)->willReturn(array('foo' => 'bar'));

        $this->addExceptionParser($exceptionParser);

        $this->processException($exception1)->shouldReturn(array('{{ foo }}' => 'bar'));
    }

    public function it_numerates_exception_params_when_using_a_null_returning_callback(ExceptionParserInterface $exceptionParser)
    {
        $this->beConstructedWith(
             function () {
                 return null;
             }
        );

        $exception1 = new \Exception('Oh no');

        $exceptionParser->accepts($exception1)->willReturn(true);
        $exceptionParser->parseException($exception1)->willReturn(array('foo' => 'bar'));

        $this->addExceptionParser($exceptionParser);

        $this->processException($exception1)->shouldReturn(array(0 => 'bar'));
    }
}
