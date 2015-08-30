<?php

namespace spec\Ob\Di;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ContainerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Ob\Di\Container');
    }

    function it_can_be_constructed_with_parameters()
    {
        $this->beConstructedWith([
            'foo' => 'bar',
            'baz' => 'qux',
        ]);

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
        $this->setParam('param', 'foo');
        $this->hasParam('param')->shouldBe(true);

        $this->unsetParam('param');
        $this->hasParam('param')->shouldBe(false);
    }

    function it_can_register_services()
    {
        $this->set('service', function () {
            return new \stdClass();
        });

        $this->get('service')->shouldHaveType('\stdClass');
    }

    function it_can_check_if_a_service_exists()
    {
        $this->has('service')->shouldReturn(false);

        $this->set('service', function () {
            return new \stdClass();
        });

        $this->has('service')->shouldReturn(true);
    }

    function it_should_keep_instances_of_created_services()
    {
        $this->set('service', function () {
            return new \stdClass();
        });

        // Get a service
        $service = $this->getWrappedObject()->get('service');

        // Get it again and make sure it's the same instance
        $this->get('service')->shouldReturn($service);
    }

    function it_can_redefine_a_service()
    {
        $this->set('service', function () {
            return new \stdClass();
        });

        $service = $this->getWrappedObject()->get('service');

        $this->set('service', function () {
            return new \stdClass();
        });

        // Make sure it's not the same instance
        $this->get('service')->shouldHaveType('\stdClass');
        $this->get('service')->shouldNotReturn($service);
    }

    function it_can_create_parameterized_services()
    {
        $this->beConstructedWith(['foo' => 'bar']);

        $this->set('service', function ($container) {
            return new Service($container->getParam('foo'));
        });

        $this->get('service')->getParam()->shouldBe('bar');
    }

    function it_can_register_a_service_factory()
    {
        $this->beConstructedWith(['foo' => 'bar']);

        $this->factory('service', function () {
            return new Service();
        });
        $this->has('service')->shouldBe(true);

        $service = $this->get('service');
        $this->get('service')->shouldNotReturn($service);
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
}
