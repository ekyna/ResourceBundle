<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Doctrine\Handler;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\Mapping\ClassMetadata;
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
    const SEPARATOR = '/';

    /**
     * @var EntityManager
     */
    protected $om;

    /**
     * @var SluggableListener
     */
    protected $sluggable;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var string
     */
    private $suffix;

    /**
     * True if node is being inserted
     *
     * @var bool
     */
    private $isInsert = false;

    /**
     * Transliterated parent slug
     *
     * @var string
     */
    private $parentSlug;

    /**
     * Used path separator
     *
     * @var string
     */
    private $usedPathSeparator;


    /**
     * {@inheritDoc}
     */
    public function __construct(SluggableListener $sluggable)
    {
        $this->sluggable = $sluggable;
    }

    /**
     * {@inheritDoc}
     */
    public function onChangeDecision(SluggableAdapter $ea, array &$config, $object, &$slug, &$needToChangeSlug)
    {
        $this->om = $ea->getObjectManager();
        $this->isInsert = $this->om->getUnitOfWork()->isScheduledForInsert($object);
        $options = $config['handlers'][get_called_class()];

        $this->usedPathSeparator = isset($options['separator']) ? $options['separator'] : self::SEPARATOR;
        $this->prefix = isset($options['prefix']) ? $options['prefix'] : '';
        $this->suffix = isset($options['suffix']) ? $options['suffix'] : '';

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
     * {@inheritDoc}
     */
    public function postSlugBuild(SluggableAdapter $ea, array &$config, $object, &$slug)
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
            if (isset($options['suffix'])) {
                $suffix = $options['suffix'];

                if (substr($this->parentSlug, -strlen($suffix)) === $suffix) { //endsWith
                    $this->parentSlug = substr_replace($this->parentSlug, '', -strlen($suffix));
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function validate(array $options, ClassMetadata $meta)
    {
        if (!$meta->isSingleValuedAssociation($options['relationField'])) {
            throw new InvalidMappingException(
                'Unable to find tree parent slug relation through field - ' .
                "[{$options['relationParentRelationField']}] in class - {$meta->name}"
            );
        }
//      TODO Check parent relation in translatable entity is single valued
//      (Note: don't know if that's possible here as we need the relationField class metadada)
    }

    /**
     * {@inheritDoc}
     */
    public function onSlugCompletion(SluggableAdapter $ea, array &$config, $object, &$slug)
    {
        $slug = $this->transliterate($slug, $config['separator'], $object);

        if (!$this->isInsert) {
            $config['pathSeparator'] = $this->usedPathSeparator;

            $wrapped = AbstractWrapper::wrap($object, $this->om);
            $meta = $wrapped->getMetadata();

            // Overwrite original data
            $uow = $this->om->getUnitOfWork();
            $changeSet = $uow->getEntityChangeSet($object);
            $oldSlug = isset($changeSet[$config['slug']])
                ? $changeSet[$config['slug']][0]
                : $meta->getReflectionProperty($config['slug'])->getValue($object);
            $oid = spl_object_hash($object);
            $ea->setOriginalObjectProperty($uow, $oid, $config['slug'], $oldSlug);

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
                    $oid = spl_object_hash($object);
                    $objectSlug = $meta->getReflectionProperty($config['slug'])->getValue($object);
                    if (preg_match("@^{$oldSlug}{$config['pathSeparator']}@smi", $objectSlug)) {
                        $objectSlug = str_replace($oldSlug, $slug, $objectSlug);
                        $meta->getReflectionProperty($config['slug'])->setValue($object, $objectSlug);
                        $ea->setOriginalObjectProperty($uow, $oid, $config['slug'], $objectSlug);
                    }
                }
            }
        }
    }

    /**
     * Transliterates the slug and prefixes the slug
     * by collection of parent slugs
     *
     * @param string $text
     * @param string $separator
     * @param object $object
     *
     * @return string
     */
    public function transliterate($text, $separator, $object)
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

    /**
     * {@inheritDoc}
     */
    public function handlesUrlization()
    {
        return false;
    }
}
