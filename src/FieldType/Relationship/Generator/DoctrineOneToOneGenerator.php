<?php

/*
 * This file is part of the SexyField package.
 *
 * (c) Dion Snoeijen <hallo@dionsnoeijen.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare (strict_types=1);

namespace Tardigrades\FieldType\Relationship\Generator;

use Doctrine\Common\Util\Inflector;
use Tardigrades\Entity\FieldInterface;
use Tardigrades\Entity\SectionInterface;
use Tardigrades\FieldType\Generator\GeneratorInterface;
use Tardigrades\FieldType\ValueObject\Template;
use Tardigrades\FieldType\ValueObject\TemplateDir;
use Tardigrades\SectionField\Generator\Loader\TemplateLoader;
use Tardigrades\SectionField\Service\SectionManagerInterface;
use Tardigrades\SectionField\ValueObject\Handle;
use Tardigrades\SectionField\ValueObject\SectionConfig;

/**
 * Class DoctrineOneToOneGenerator
 *
 * @todo: We have an automatic inverse relationship detector in
 * the generators. What we have to take care of is that the opposing
 * relationship for a many to many field in case of a unidirectional
 * opposing side get's the correct opposing field added. With type
 * bidirectional.
 *
 * @package Tardigrades\FieldTypeInterface\Relationship\Generator
 */
class DoctrineOneToOneGenerator implements GeneratorInterface
{
    const KIND = 'one-to-one';

    public static function generate(FieldInterface $field, TemplateDir $templateDir, ...$options): Template
    {
        $fieldConfig = $field->getConfig()->toArray();

        /** @var SectionManagerInterface $sectionManager */
        $sectionManager = $options[0]['sectionManager'];

        /** @var SectionConfig $sectionConfig */
        $sectionConfig = $options[0]['sectionConfig'];

        if ($fieldConfig['field']['kind'] === self::KIND) {

            /** @var SectionInterface $from */
            $from = $sectionManager->readByHandle(Handle::fromString($fieldConfig['field']['from']));

            /** @var SectionInterface $to */
            $to = $sectionManager->readByHandle(Handle::fromString($fieldConfig['field']['to']));

            $fromVersion = $from->getVersion()->toInt() > 1 ? ('_' . $from->getVersion()->toInt()) : '';
            $toVersion = $to->getVersion()->toInt() > 1 ? ('_' . $to->getVersion()->toInt()) : '';

            return Template::create(
                TemplateLoader::load(
                    (string) $templateDir . '/GeneratorTemplate/doctrine.onetoone.xml.php', [
                        'type' => $fieldConfig['field']['type'],
                        'toPluralHandle' => Inflector::pluralize($fieldConfig['field']['to']) . $toVersion,
                        'toFullyQualifiedClassName' => $to->getConfig()->getFullyQualifiedClassName(),
                        'fromHandle' => $fieldConfig['field']['handle'] . $fromVersion,
                        'fromPluralHandle' => Inflector::pluralize($fieldConfig['field']['handle']) . $fromVersion,
                        'fromFullyQualifiedClassName' => $sectionConfig->getFullyQualifiedClassName(),
                        'toHandle' => $fieldConfig['field']['to'] . $toVersion
                    ]
                )
            );
        }

        return Template::create('');
    }
}
