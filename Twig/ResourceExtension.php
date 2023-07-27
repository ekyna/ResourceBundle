<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Twig;

use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Component\Resource\Helper\EnumHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Class ResourceExtension
 * @package Ekyna\Bundle\ResourceBundle\Twig
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ResourceExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'resource_has_action',
                [ResourceHelper::class, 'hasAction']
            ),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'enum_label',
                [EnumHelper::class, 'label']
            ),
            new TwigFilter(
                'enum_badge',
                [EnumHelper::class, 'badge'],
                ['is_safe' => ['html']]
            ),
        ];
    }
}
