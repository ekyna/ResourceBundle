<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Twig;

use Ekyna\Component\Resource\Helper\EnumHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Class ResourceExtension
 * @package Ekyna\Bundle\ResourceBundle\Twig
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ResourceExtension extends AbstractExtension
{
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
