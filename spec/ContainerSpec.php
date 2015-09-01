<?php

namespace spec\Ob\Di;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ContainerSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith([
            'foo' => 'bar',
            'baz' => 'qux',
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Ob\Di\Container');
    }

    function it_can_be_constructed_with_parameters()
    {
        $this->getParam('foo')->shouldEqual('bar');
        $this->getParam('baz')->shouldEqual('qux');
    }

    function it_can_set_a_parameter()
    {
        $this->setParam('param', 'foo');
        $this->getParam('param')->shouldEqual('foo');
    }

    function it_can_check_if_a_parameter_exists()
    {
        $this->hasParam('param')->shouldBe(false);
    }

    function it_can_unset_a_parameter()
    {
        $this->unsetParam('foo');
        $this->hasParam('foo')->shouldBe(false);
    }

    function it_can_register_services()
    {
        $this->set('service', function () {
            return new Service();
        });

        $this->get('service')->shouldHaveType('spec\Ob\Di\Service');
    }

    function it_can_check_if_a_service_exists()
    {
        $this->has('service')->shouldReturn(false);

        $this->set('service', function () {
            return new Service();
        });

        $this->has('service')->shouldReturn(true);
    }

    function it_should_keep_instances_of_created_services()
    {
        $this->set('service', function () {
            return new Service();
        });

        // Get a service
        $service = $this->getWrappedObject()->get('service');

        // Get it again and make sure it's the same instance
        $this->get('service')->shouldReturn($service);
    }

    function it_can_redefine_a_service()
    {
        $this->set('service', function () {
            return new Service();
        });

        $service = $this->getWrappedObject()->get('service');

        $this->set('service', function () {
            return new Service();
        });

        // Make sure it's not the same instance
        $this->get('service')->shouldHaveType('spec\Ob\Di\Service');
        $this->get('service')->shouldNotReturn($service);
    }

    function it_can_create_parameterized_services()
    {
        $this->set('service', function ($container) {
            return new Service($container->getParam('foo'));
        });

        $this->get('service')->getParam()->shouldBe('bar');
    }

    function it_can_register_a_service_factory()
    {
        $this->factory('service', function () {
            return new Service();
        });
        $this->has('service')->shouldBe(true);

        $service = $this->get('service');
        $this->get('service')->shouldHaveType('spec\Ob\Di\Service');
        $this->get('service')->shouldNotReturn($service);
    }

    function it_can_retrieve_a_service_callable()
    {
        $callable = function () {
            return new Service();
        };
        $this->set('service', $callable);

        $this->raw('service')->shouldReturn($callable);
    }

    function it_can_register_function_as_parameter()
    {
        $this->protect('random', function () {
            return rand();
        });

        $this->getParam('random')->shouldBeInt();
    }

    function it_can_extend_a_service_definition()
    {
        $this->set('service', function () {
            return new Service('foo');
        });

        $this->extend('service', function ($service) {
            $service->setParam('bar');

            return $service;
        });

        $this->get('service')->getParam()->shouldReturn('bar');
    }
}

class Service
{
    private $param;

    public function __construct($param = null)
    {
        $this->param = $param;
    }

    public function getParam()
    {
        return $this->param;
    }

    public function setParam($value)
    {
        $this->param = $value;
    }
}
