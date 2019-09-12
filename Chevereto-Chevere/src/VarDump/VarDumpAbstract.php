<?php

declare(strict_types=1);

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevere\VarDump;

use Throwable;
use Reflector;
use ReflectionProperty;
use ReflectionObject;
use const Chevere\CLI;
use Chevere\Path\Path;
use Chevere\Utility\Str;

/**
 * Analyze a variable and provide an output string representation of its type and data.
 */
abstract class VarDumpAbstract
{
    const RUNTIME = CLI ? ConsoleVarDump::class : HtmlVarDump::class;

    const TYPE_STRING = 'string';
    const TYPE_FLOAT = 'float';
    const TYPE_INTEGER = 'integer';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_NULL = 'NULL';
    const TYPE_OBJECT = 'object';
    const TYPE_ARRAY = 'array';
    const _FILE = '_file';
    const _CLASS = '_class';
    const _OPERATOR = '_operator';
    const _FUNCTION = '_function';
    const ANON_CLASS = 'class@anonymous';

    const PROPERTIES_REFLECTION_MAP = [
        'public' => ReflectionProperty::IS_PUBLIC,
        'protected' => ReflectionProperty::IS_PROTECTED,
        'private' => ReflectionProperty::IS_PRIVATE,
        'static' => ReflectionProperty::IS_STATIC,
    ];

    /** @var string */
    protected $output;

    /** @var string */
    protected $template;

    /** @var string */
    protected $className;

    /** @var array */
    protected $properties;

    /** @var Reflector */
    protected $reflectionObject;

    protected $var;

    /** @var mixed */
    protected $expression;

    /** @var int */
    protected $indent;

    /** @var array */
    protected $dontDump;

    /** @var int */
    protected $depth;

    /** @var mixed */
    protected $val;

    /** @var string */
    protected $prefix;

    /** @var string */
    protected $type;

    /** @var string */
    protected $parentheses;

    public function __construct($var, int $indent = null, array $dontDump = [], int $depth = 0)
    {
        ++$depth;
        $this->var = $var;
        // Maybe improve this to support any circular reference?
        if (is_array($this->var)) {
            $this->expression = array_merge([], $this->var);
        } else {
            $this->expression = $this->var;
        }
        $this->indent = $indent ?? 0;
        $this->dontDump = $dontDump;
        $this->depth = $depth;
        $this->val = null;
        $this->setPrefix();
        $this->setType();
        $this->handleType();
        $this->setTemplate();
        $this->handleParentheses();
        $this->output = strtr($this->template, [
            '%type' => static::wrap($this->type, $this->type),
            '%val' => $this->val,
            '%parentheses' => isset($this->parentheses) ? static::wrap(static::_OPERATOR, '(' . $this->parentheses . ')') : null,
        ]);
    }

    abstract protected function setPrefix(): void;

    abstract protected function getEmphasis(string $string): string;

    abstract protected function filterChars(string $string): string;

    abstract public static function wrap(string $key, string $dump): ?string;

    protected function setType(): void
    {
        $this->type = gettype($this->expression);
        if ('double' == $this->type) {
            $this->type = static::TYPE_FLOAT;
        }
    }

    protected function handleType(): void
    {
        switch ($this->type) {
            case static::TYPE_BOOLEAN:
                $this->val .= $this->expression ? 'TRUE' : 'FALSE';
                break;
            case static::TYPE_ARRAY:
                ++$this->indent;
                $this->processArray();
                break;
            case static::TYPE_OBJECT:
                ++$this->indent;
                $this->processObject();
                break;
            default:
                $this->processDefault();
                break;
        }
    }

    protected function processObject(): void
    {
        $this->reflectionObject = new ReflectionObject($this->expression);
        if (in_array($this->reflectionObject->getName(), $this->dontDump)) {
            $this->val .= static::wrap(static::_OPERATOR, $this->getEmphasis($this->reflectionObject->getName()));

            return;
        }
        $this->setProperties();
        foreach ($this->properties as $k => $v) {
            $this->processObjectProperty($k, $v);
        }
        $this->className = get_class($this->expression);
        $this->handleNormalizeClassName();
        $this->parentheses = $this->className;
    }

    protected function setProperties(): void
    {
        $this->properties = [];
        foreach (static::PROPERTIES_REFLECTION_MAP as $visibility => $filter) {
            /** @scrutinizer ignore-call */
            $properties = $this->reflectionObject->getProperties($filter);
            foreach ($properties as $property) {
                if (!isset($this->properties[$property->getName()])) {
                    $property->setAccessible(true);
                    // var_dump($this->reflectionObject->getProperty($property->getName()));
                    try {
                        $value = $property->getValue($this->expression);
                    } catch (Throwable $e) {
                        // $value = '';
                    }
                        
                    $this->properties[$property->getName()] = ['value' => $value];
                }
                $this->properties[$property->getName()]['visibility'][] = $visibility;
            }
        }
    }

    protected function processObjectProperty($key, $var): void
    {
        $visibility = implode(' ', $var['visibility'] ?? $this->properties['visibility']);
        $operator = static::wrap(static::_OPERATOR, '->');
        $this->val .= "\n" . $this->prefix . $this->getEmphasis($visibility) . ' ' . $this->filterChars($key) . " $operator ";
        $aux = $var['value'];
        if (is_object($aux) && property_exists($aux, $key)) {
            try {
                $r = new ReflectionObject($aux);
                $p = $r->getProperty($key);
                $p->setAccessible(true);
                if ($aux == $p->getValue($aux)) {
                    $this->val .= static::wrap(static::_OPERATOR, '(' . $this->getEmphasis('circular object reference') . ')');
                }

                return;
            } catch (Throwable $e) {
                return;
            }
        }
        if ($this->depth < 4) {
            $this->val .= (new static($aux, $this->indent, $this->dontDump, $this->depth))->toString();
        } else {
            $this->val .= static::wrap(static::_OPERATOR, '(' . $this->getEmphasis('max depth reached') . ')');
        }
    }

    protected function processArray(): void
    {
        foreach ($this->expression as $k => $v) {
            $operator = static::wrap(static::_OPERATOR, '=>');
            $this->val .= "\n" . $this->prefix . ' ' . $this->filterChars((string) $k) . " $operator ";
            $aux = $v;
            $isCircularRef = is_array($aux) && isset($aux[$k]) && $aux == $aux[$k];
            if ($isCircularRef) {
                $this->val .= static::wrap(static::_OPERATOR, '(' . $this->getEmphasis('circular array reference') . ')');
            } else {
                $this->val .= (new static($aux, $this->indent, $this->dontDump))->toString();
            }
        }
        $this->parentheses = 'size=' . count($this->expression);
    }

    protected function handleNormalizeClassName(): void
    {
        if (Str::startsWith(static::ANON_CLASS, $this->className)) {
            $this->className = Path::normalize($this->className);
        }
    }

    protected function processDefault(): void
    {
        $is_string = is_string($this->expression);
        $is_numeric = is_numeric($this->expression);
        if ($is_string || $is_numeric) {
            $this->parentheses = 'length=' . strlen($is_numeric ? ((string) $this->expression) : $this->expression);
            $this->val .= $this->filterChars(strval($this->expression));
        }
    }

    protected function setTemplate(): void
    {
        switch ($this->type) {
            case static::TYPE_ARRAY:
            case static::TYPE_OBJECT:
                $this->template = '%type %parentheses%val';
                break;
            default:
                $this->template = '%type %val %parentheses';
                break;
        }
    }

    protected function handleParentheses(): void
    {
        if (isset($this->parentheses) && false !== strpos($this->parentheses, '=')) {
            $this->parentheses = $this->getEmphasis($this->parentheses);
        }
    }

    public function toString(): string
    {
        return $this->output ?? '';
    }
}