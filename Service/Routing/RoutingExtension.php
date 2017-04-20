<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Service\Routing;

use Ekyna\Component\Resource\Config\Resolver\DefaultsResolver;
use Ekyna\Component\Resource\Extension\AbstractExtension;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_map;
use function in_array;
use function preg_match;
use function strtoupper;

/**
 * Class RoutingExtension
 * @package Ekyna\Bundle\ResourceBundle\Service\Routing
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RoutingExtension extends AbstractExtension
{
    private const ROUTE_REGEX  = '~^[a-z][a-z0-9]*(_[a-z0-9]+)*[a-z0-9]*_%s_[a-z][a-z0-9]*(_[a-z0-9]+)*[a-z0-9]*$~';
    private const PATH_REGEX   = '~(^/\{?[a-z][a-z0-9]*(-[a-z0-9]+)*[a-z0-9]*\}?)*$~';
    private const HTTP_METHODS = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];


    /**
     * @inheritDoc
     *
     * @see \Ekyna\Bundle\ResourceBundle\Service\Routing\ResourceLoader
     */
    public function extendActionConfig(OptionsResolver $resolver, DefaultsResolver $defaults): void
    {
        $b = [
            'path'     => '',
            'resource' => false,
            'methods'  => ['GET'],
        ];

        $routeResolver = new OptionsResolver();
        $routeResolver
            ->setRequired(['name'])
            ->setDefaults($b)
            ->setAllowedTypes('name', 'string')
            ->setAllowedTypes('path', 'string')
            ->setAllowedTypes('resource', 'bool')
            ->setAllowedTypes('methods', ['string', 'array'])
            ->setAllowedValues('name', function ($value) {
                if (!preg_match(self::ROUTE_REGEX, $value)) {
                    throw new InvalidOptionsException("Invalid action route '$value'.");
                }

                return true;
            })
            ->setAllowedValues('path', function ($value) {
                if (!preg_match(self::PATH_REGEX, $value)) {
                    throw new InvalidOptionsException("Invalid action path '$value'.");
                }

                return true;
            })
            ->setAllowedValues('methods', function ($value) {
                $value = (array)$value;

                foreach ($value as $method) {
                    if (!in_array(strtoupper($method), self::HTTP_METHODS, true)) {
                        return false;
                    }
                }

                return true;
            })
            ->setNormalizer('methods', function (Options $options, $value) {
                return array_map(function ($method) {
                    return strtoupper($method);
                }, (array)$value);
            });

        $defaults->add([
            'route' => $b,
        ]);

        $resolver
            ->setDefined('route')
            ->setAllowedTypes('route', ['array', 'null'])
            ->setNormalizer('route', function (Options $options, $value) use ($routeResolver) {
                if (empty($value)) {
                    return null;
                }

                return $routeResolver->resolve($value);
            });
    }

    public function extendActionOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined('expose')
            ->setAllowedTypes('expose', 'bool');
    }
}
