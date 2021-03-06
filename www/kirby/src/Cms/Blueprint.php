<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Data\Data;
use Kirby\Form\Field;
use Kirby\Toolkit\F;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Obj;

/**
 * The Blueprint class normalizes an array from a
 * blueprint file and converts sections, columns, fields
 * etc. into a correct tab layout.
 */
class Blueprint
{
    public static $presets = [];
    public static $loaded = [];

    protected $fields = [];
    protected $model;
    protected $props;
    protected $sections = [];
    protected $tabs = [];

    /**
     * Magic getter/caller for any blueprint prop
     *
     * @param string $key
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $key, array $arguments = null)
    {
        return $this->props[$key] ?? null;
    }

    /**
     * Creates a new blueprint object with the given props
     *
     * @param array $props
     */
    public function __construct(array $props)
    {
        if (empty($props['model']) === true) {
            throw new InvalidArgumentException('A blueprint model is required');
        }

        $this->model = $props['model'];

        // the model should not be included in the props array
        unset($props['model']);

        // extend the blueprint in general
        $props = $this->extend($props);

        // apply any blueprint preset
        $props = $this->preset($props);

        // normalize the name
        $props['name'] = $props['name'] ?? 'default';

        // normalize and translate the title
        $props['title'] = $this->i18n($props['title'] ?? ucfirst($props['name']));

        // convert all shortcuts
        $props = $this->convertFieldsToSections('main', $props);
        $props = $this->convertSectionsToColumns('main', $props);
        $props = $this->convertColumnsToTabs('main', $props);

        // normalize all tabs
        $props['tabs'] = $this->normalizeTabs($props['tabs'] ?? []);

        $this->props = $props;
    }

    /**
     * Improved var_dump output
     *
     * @return array
     */
    public function __debuginfo(): array
    {
        return $this->props;
    }

    /**
     * Converts all column definitions, that
     * are not wrapped in a tab, into a generic tab
     *
     * @param string $tabName
     * @param array $props
     * @return array
     */
    protected function convertColumnsToTabs(string $tabName, array $props): array
    {
        if (isset($props['columns']) === false) {
            return $props;
        }

        // wrap everything in a main tab
        $props['tabs'] = [
            $tabName => [
                'columns' => $props['columns']
            ]
        ];

        unset($props['columns']);

        return $props;
    }

    /**
     * Converts all field definitions, that are not
     * wrapped in a fields section into a generic
     * fields section.
     *
     * @param string $tabName
     * @param array $props
     * @return array
     */
    protected function convertFieldsToSections(string $tabName, array $props): array
    {
        if (isset($props['fields']) === false) {
            return $props;
        }

        // wrap all fields in a section
        $props['sections'] = [
            $tabName . '-fields' => [
                'type'   => 'fields',
                'fields' => $props['fields']
            ]
        ];

        unset($props['fields']);

        return $props;
    }

    /**
     * Converts all sections that are not wrapped in
     * columns, into a single generic column.
     *
     * @param string $tabName
     * @param array $props
     * @return array
     */
    protected function convertSectionsToColumns(string $tabName, array $props): array
    {
        if (isset($props['sections']) === false) {
            return $props;
        }

        // wrap everything in one big column
        $props['columns'] = [
            [
                'width'    => '1/1',
                'sections' => $props['sections']
            ]
        ];

        unset($props['sections']);

        return $props;
    }

    /**
     * Extends the props with props from a given
     * mixin, when an extends key is set or the
     * props is just a string
     *
     * @param array|string $props
     * @return array
     */
    public function extend($props): array
    {
        if (is_string($props) === true) {
            $props = [
                'extends' => $props
            ];
        }

        $extends = $props['extends'] ?? null;

        if ($extends === null) {
            return $props;
        }

        $mixin = static::find($extends);

        if ($mixin === null) {
            return $props;
        }

        if (is_array($mixin) === true) {
            return array_replace_recursive($mixin, $props);
        }

        try {
            return array_replace_recursive(Data::read($mixin), $props);
        } catch (Exception $e) {
            return $props;
        }
    }

    /**
     * Create a new blueprint for a model
     *
     * @param string $name
     * @param string $fallback
     * @param Model $model
     * @return self
     */
    public static function factory(string $name, string $fallback = null, Model $model)
    {
        try {
            $props = static::load($name);
        } catch (Exception $e) {
            $props = $fallback !== null ? static::load($fallback) : null;
        }

        if ($props === null) {
            return null;
        }

        // inject the parent model
        $props['model'] = $model;

        return new static($props);
    }

    /**
     * Returns a single field definition by name
     *
     * @param string $name
     * @return array|null
     */
    public function field(string $name): ?array
    {
        return $this->fields[$name] ?? null;
    }

    /**
     * Returns all field definitions
     *
     * @return array
     */
    public function fields(): array
    {
        return $this->fields;
    }

    /**
     * Find a blueprint by name
     *
     * @param string $name
     * @return string|array
     */
    public static function find(string $name)
    {
        $kirby = App::instance();
        $root  = $kirby->root('blueprints');
        $file  = $root . '/' . $name . '.yml';

        if (F::exists($file, $root) === true) {
            return $file;
        }

        if ($blueprint = $kirby->extension('blueprints', $name)) {
            return $blueprint;
        }

        throw new NotFoundException([
            'key'  => 'blueprint.notFound',
            'data' => ['name' => $name]
        ]);
    }

    /**
     * Used to translate any label, heading, etc.
     *
     * @param mixed $value
     * @param mixed $fallback
     * @return mixed
     */
    protected function i18n($value, $fallback = null)
    {
        return I18n::translate($value, $fallback ?? $value);
    }

    /**
     * Checks if this is the default blueprint
     *
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->name() === 'default';
    }

    /**
     * Loads a blueprint from file or array
     *
     * @param string $name
     * @param string $fallback
     * @param Model $model
     * @return array
     */
    public static function load(string $name)
    {
        if (isset(static::$loaded[$name]) === true) {
            return static::$loaded[$name];
        }

        $props     = static::find($name);
        $normalize = function ($props) use ($name) {

            // inject the filename as name if no name is set
            $props['name'] = $props['name'] ?? $name;

            // normalize the title
            $title = $props['title'] ?? ucfirst($props['name']);

            // translate the title
            $props['title'] = I18n::translate($title, $title);

            return $props;
        };

        if (is_array($props) === true) {
            return $normalize($props);
        }

        $file  = $props;
        $props = Data::read($file);

        return static::$loaded[$name] = $normalize($props);
    }

    /**
     * Returns the parent model
     *
     * @return Model
     */
    public function model()
    {
        return $this->model;
    }

    /**
     * Returns the blueprint name
     *
     * @return string
     */
    public function name(): string
    {
        return $this->props['name'];
    }

    /**
     * Normalizes all required props in a column setup
     *
     * @param string $tabName
     * @param array $columns
     * @return array
     */
    protected function normalizeColumns(string $tabName, array $columns): array
    {
        foreach ($columns as $columnKey => $columnProps) {
            $columnProps = $this->convertFieldsToSections($tabName . '-col-' . $columnKey, $columnProps);

            // inject getting started info, if the sections are empty
            if (empty($columnProps['sections']) === true) {
                $columnProps['sections'] = [
                    $tabName . '-info-' . $columnKey => [
                        'headline' => 'Column (' . ($columnProps['width'] ?? '1/1') . ')',
                        'type'     => 'info',
                        'text'     => 'No sections yet'
                    ]
                ];
            }

            $columns[$columnKey] = array_merge($columnProps, [
                'width'    => $columnProps['width'] ?? '1/1',
                'sections' => $this->normalizeSections($tabName, $columnProps['sections'] ?? [])
            ]);
        }

        return $columns;
    }

    protected function helpList(array $items)
    {
        $md = [];

        foreach ($items as $item) {
            $md[] = '- *' . $item . '*';
        }

        return PHP_EOL . implode(PHP_EOL, $md);
    }

    /**
     * Normalize field props for a single field
     *
     * @param string $name
     * @param string $type
     * @param array $props
     * @return array
     */
    protected function normalizeField(string $name, string $type, array $props): array
    {
        if (isset(Field::$types[$type]) === false) {
            return [
                'name'  => $name,
                'label' => 'Invalid field type ("' . $type . '")',
                'type'  => 'info',
                'text'  => 'The following field types are available: ' . $this->helpList(array_keys(Field::$types))
            ];
        }

        return array_merge($props, [
            'label' => $props['label'] ?? ucfirst($name),
            'name'  => $name,
            'type'  => $type,
            'width' => $props['width'] ?? '1/1',
        ]);
    }

    /**
     * Normalizes all fields and adds automatic labels,
     * types and widths.
     *
     * @param string $tabName
     * @param array $fields
     * @return array
     */
    protected function normalizeFields(string $tabName, $fields, int $level = 0): array
    {
        if (is_array($fields) === false) {
            $fields = [];
        }

        foreach ($fields as $fieldName => $fieldProps) {
            if ($fieldProps === true) {
                $fieldProps = [];
            }

            // inject all field extensions
            $fieldProps = $this->extend($fieldProps);

            // support for nested fields
            if (isset($fieldProps['fields']) === true) {
                $fieldProps['fields'] = $this->normalizeFields($tabName, $fieldProps['fields'], $level + 1);
            }

            // inject the name as field type if it does not exist
            $fieldType = $fieldProps['type'] ?? $fieldName;

            // resolve field groups
            if ($fieldType === 'group') {
                if (empty($fieldProps['fields']) === false && is_array($fieldProps['fields']) === true) {
                    $index  = array_search($fieldName, array_keys($fields));
                    $before = array_slice($fields, 0, $index);
                    $after  = array_slice($fields, $index + 1);
                    $fields = array_merge($before, $fieldProps['fields'] ?? []);
                } else {
                    unset($fields[$fieldName]);
                }
            } else {
                $fields[$fieldName] = $this->normalizeField($fieldName, $fieldType, $fieldProps);
            }
        }

        // store all normalized fields if not in nested fields
        if ($level === 0) {
            $this->fields = array_merge($this->fields, $fields);
        }

        return $fields;
    }

    /**
     * Normalizes blueprint options. This must be used in the
     * constructor of an extended class, if you want to make use of it.
     *
     * @param array|true|false|null $options
     * @param array $defaults
     * @param array $aliases
     * @return array
     */
    protected function normalizeOptions($options, array $defaults, array $aliases = []): array
    {
        // return defaults when options are not defined or set to true
        if ($options === true) {
            return $defaults;
        }

        // set all options to false
        if ($options === false) {
            return array_map(function () {
                return false;
            }, $defaults);
        }

        foreach ($options as $key => $value) {
            $alias = $aliases[$key] ?? null;

            if ($alias !== null) {
                $options[$alias] = $options[$alias] ?? $value;
                unset($options[$key]);
            }
        }

        return array_merge($defaults, $options);
    }

    /**
     * Normalizes all required keys in sections
     *
     * @param string $tabName
     * @param array $sections
     * @return array
     */
    protected function normalizeSections(string $tabName, array $sections): array
    {
        foreach ($sections as $sectionName => $sectionProps) {

            // inject all section extensions
            $sectionProps = $this->extend($sectionProps);

            $sections[$sectionName] = $sectionProps = array_merge($sectionProps, [
                'name' => $sectionName,
                'type' => $type = $sectionProps['type'] ?? null
            ]);

            if (isset(Section::$types[$type]) === false) {
                $sections[$sectionName] = [
                    'name' => $sectionName,
                    'headline' => 'Invalid section type ("' . $type . '")',
                    'type' => 'info',
                    'text' => 'The following section types are available: ' . $this->helpList(array_keys(Section::$types))
                ];
            }

            if ($sectionProps['type'] === 'fields') {
                $fields = $this->normalizeFields($tabName, $sectionProps['fields'] ?? []);

                // inject guide fields guide
                if (empty($fields) === true) {
                    $fields = [
                        $tabName . '-info' => [
                            'label' => 'Fields',
                            'text'  => 'No fields yet',
                            'type'  => 'info'
                        ]
                    ];
                }

                $sections[$sectionName]['fields'] = $fields;
            }
        }

        // store all normalized sections
        $this->sections = array_merge($this->sections, $sections);

        return $sections;
    }

    /**
     * Normalizes all required keys in tabs
     *
     * @param array $tabs
     * @return array
     */
    protected function normalizeTabs($tabs): array
    {
        if (is_array($tabs) === false) {
            $tabs = [];
        }

        foreach ($tabs as $tabName => $tabProps) {

            // inject all tab extensions
            $tabProps = $this->extend($tabProps);

            // inject a preset if available
            $tabProps = $this->preset($tabProps);

            $tabProps = $this->convertFieldsToSections($tabName, $tabProps);
            $tabProps = $this->convertSectionsToColumns($tabName, $tabProps);

            $tabs[$tabName] = array_merge($tabProps, [
                'columns' => $this->normalizeColumns($tabName, $tabProps['columns'] ?? []),
                'icon'    => $tabProps['icon']  ?? null,
                'label'   => $this->i18n($tabProps['label'] ?? ucfirst($tabName)),
                'name'    => $tabName,
            ]);
        }

        return $this->tabs = $tabs;
    }

    /**
     * Injects a blueprint preset
     *
     * @param array $props
     * @return array
     */
    protected function preset(array $props): array
    {
        if (isset($props['preset']) === false) {
            return $props;
        }

        if (isset(static::$presets[$props['preset']]) === false) {
            return $props;
        }

        return static::$presets[$props['preset']]($props);
    }

    /**
     * Returns a single section by name
     *
     * @param string $name
     * @return Section|null
     */
    public function section(string $name): ?Section
    {
        if (empty($this->sections[$name]) === true) {
            return null;
        }

        // get all props
        $props = $this->sections[$name];

        // inject the blueprint model
        $props['model'] = $this->model();

        // create a new section object
        return new Section($props['type'], $props);
    }

    /**
     * Returns all sections
     *
     * @return array
     */
    public function sections(): array
    {
        return array_map(function ($section) {
            return $this->section($section['name']);
        }, $this->sections);
    }

    /**
     * Returns a single tab by name
     *
     * @param string $name
     * @return array|null
     */
    public function tab(string $name): ?array
    {
        return $this->tabs[$name] ?? null;
    }

    /**
     * Returns all tabs
     *
     * @return array
     */
    public function tabs(): array
    {
        return array_values($this->tabs);
    }

    /**
     * Returns the blueprint title
     *
     * @return string
     */
    public function title(): string
    {
        return $this->props['title'];
    }

    /**
     * Converts the blueprint object to a plain array
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->props;
    }
}
