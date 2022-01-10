<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Doctrine\Handler;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use Gedmo\Exception\InvalidMappingException;
use Gedmo\Sluggable\Handler\SlugHandlerInterface;
use Gedmo\Sluggable\Mapping\Event\SluggableAdapter;
use Gedmo\Sluggable\SluggableListener;
use Gedmo\Tool\Wrapper\AbstractWrapper;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Class TreeTranslationSlugHandler
 * @package Ekyna\Bundle\AdminBundle\Doctrine\Handler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TreeTranslationSlugHandler implements SlugHandlerInterface
{
    public const SEPARATOR = '/';

    protected SluggableListener $sluggable;

    protected ObjectManager $om;
    private string          $prefix;
    private string          $suffix;
    private bool            $isInsert = false;
    private string          $parentSlug;
    private string          $usedPathSeparator;

    public function __construct(SluggableListener $sluggable)
    {
        $this->sluggable = $sluggable;
    }

    /**
     * @inheritDoc
     */
    public function onChangeDecision(SluggableAdapter $ea, array &$config, $object, &$slug, &$needToChangeSlug): void
    {
        $this->om = $ea->getObjectManager();
        $this->isInsert = $this->om->getUnitOfWork()->isScheduledForInsert($object);
        $options = $config['handlers'][get_called_class()];

        $this->usedPathSeparator = $options['separator'] ?? self::SEPARATOR;
        $this->prefix = $options['prefix'] ?? '';
        $this->suffix = $options['suffix'] ?? '';

        if (!$this->isInsert && !$needToChangeSlug) {
            $changeSet = $ea->getObjectChangeSet($this->om->getUnitOfWork(), $object);
            if (isset($changeSet[$options['relationParentRelationField']])) {
                $needToChangeSlug = true;
            }
        }

        if ($needToChangeSlug) {
            $language = new ExpressionLanguage();
            if (isset($options['skipExpression']) && !empty($expression = $options['skipExpression'])) {
                if ($language->evaluate($expression, ['object' => $object])) {
                    $needToChangeSlug = false;
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function postSlugBuild(SluggableAdapter $ea, array &$config, $object, &$slug): void
    {
        $options = $config['handlers'][get_called_class()];
        $this->parentSlug = '';

        $wrapped = AbstractWrapper::wrap($object, $this->om);

        $relation = $wrapped->getPropertyValue($options['relationField']);
        $locale = $wrapped->getPropertyValue($options['locale']);

        $wrapped = AbstractWrapper::wrap($relation, $this->om);
        if ($parent = $wrapped->getPropertyValue($options['relationParentRelationField'])) {
            if (isset($options['parentSkipExpression']) && !empty($expression = $options['parentSkipExpression'])) {
                $language = new ExpressionLanguage();
                if ($language->evaluate($expression, ['parent' => $parent])) {
                    return;
                }
            }

            $translation = call_user_func_array([$parent, $options['translate']], [$locale]);

            $this->parentSlug = $translation->{$options['parentFieldMethod']}();

            // if needed, remove suffix from parentSlug, so we can use it to prepend it to our slug
            if (isset($options['suffix']) && !empty($options['suffix'])) {
                $suffix = $options['suffix'];

                if (substr($this->parentSlug, -strlen($suffix)) === $suffix) { //endsWith
                    $this->parentSlug = substr_replace($this->parentSlug, '', -strlen($suffix));
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public static function validate(array $options, ClassMetadata $meta): void
    {
        if (!$meta->isSingleValuedAssociation($options['relationField'])) {
            throw new InvalidMappingException(
                'Unable to find tree parent slug relation through field - ' .
                "[{$options['relationParentRelationField']}] in class - $meta->name"
            );
        }
//      TODO Check parent relation in translatable entity is single valued
//      (Note: don't know if that's possible here as we need the relationField class metadada)
    }

    /**
     * @inheritDoc
     */
    public function onSlugCompletion(SluggableAdapter $ea, array &$config, $object, &$slug): void
    {
        $slug = $this->transliterate($slug);

        if ($this->isInsert) {
            return;
        }

        $config['pathSeparator'] = $this->usedPathSeparator;

        $wrapped = AbstractWrapper::wrap($object, $this->om);
        $meta = $wrapped->getMetadata();

        // Overwrite original data
        $uow = $this->om->getUnitOfWork();
        $changeSet = $uow->getEntityChangeSet($object);
        $oldSlug = isset($changeSet[$config['slug']])
            ? $changeSet[$config['slug']][0]
            : $meta->getReflectionProperty($config['slug'])->getValue($object);
        $ea->setOriginalObjectProperty($uow, $object, $config['slug'], $oldSlug);

        // Translatable children paths replacement
        if (!(isset($config['replaceChildren']) && $config['replaceChildren'])) {
            return;
        }

        $ea->replaceRelative($object, $config, $oldSlug . $config['pathSeparator'], $slug);

        // Update in memory objects
        foreach ($uow->getIdentityMap() as $className => $objects) {
            // for inheritance mapped classes, only root is always in the identity map
            if ($className !== $wrapped->getRootObjectName()) {
                continue;
            }

            foreach ($objects as $object) {
                if (property_exists($object, '__isInitialized__') && !$object->__isInitialized__) {
                    continue;
                }

                $objectSlug = $meta->getReflectionProperty($config['slug'])->getValue($object);
                if (preg_match("@^{$oldSlug}{$config['pathSeparator']}@smi", $objectSlug)) {
                    $objectSlug = str_replace($oldSlug, $slug, $objectSlug);
                    $meta->getReflectionProperty($config['slug'])->setValue($object, $objectSlug);
                    $ea->setOriginalObjectProperty($uow, $object, $config['slug'], $objectSlug);
                }
            }
        }
    }

    private function transliterate(string $text): string
    {
        $slug = $text . $this->suffix;

        if (strlen($this->parentSlug)) {
            $slug = $this->parentSlug . $this->usedPathSeparator . $slug;
        } else {
            // if no parentSlug, apply our prefix
            $slug = $this->prefix . $slug;
        }

        return $slug;
    }

    public function handlesUrlization(): bool
    {
        return false;
    }
}
