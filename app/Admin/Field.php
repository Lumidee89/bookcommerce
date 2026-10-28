<?php

namespace App\Admin;

/**
 * Fluent description of an admin form field, serialised to the generic React form.
 */
class Field
{
    public array $props = [];

    private function __construct(public string $name, public string $type, string $label)
    {
        $this->props = ['name' => $name, 'type' => $type, 'label' => $label];
    }

    public static function make(string $name, string $type, string $label): self
    {
        return new self($name, $type, $label);
    }

    public static function text(string $name, string $label): self
    {
        return new self($name, 'text', $label);
    }

    public static function email(string $name, string $label): self
    {
        return new self($name, 'email', $label);
    }

    public static function url(string $name, string $label): self
    {
        return new self($name, 'url', $label);
    }

    public static function textarea(string $name, string $label): self
    {
        return new self($name, 'textarea', $label);
    }

    public static function markdown(string $name, string $label): self
    {
        return new self($name, 'markdown', $label);
    }

    public static function number(string $name, string $label): self
    {
        return new self($name, 'number', $label);
    }

    public static function money(string $name, string $label): self
    {
        return new self($name, 'money', $label);
    }

    public static function checkbox(string $name, string $label): self
    {
        return new self($name, 'checkbox', $label);
    }

    public static function date(string $name, string $label): self
    {
        return new self($name, 'date', $label);
    }

    public static function datetime(string $name, string $label): self
    {
        return new self($name, 'datetime', $label);
    }

    public static function time(string $name, string $label): self
    {
        return new self($name, 'time', $label);
    }

    public static function tags(string $name, string $label): self
    {
        return new self($name, 'tags', $label);
    }

    public static function image(string $name, string $label): self
    {
        return (new self($name, 'image', $label))->accept('image/*');
    }

    public static function file(string $name, string $label): self
    {
        return new self($name, 'file', $label);
    }

    public static function heading(string $label): self
    {
        return new self('_heading_'.md5($label), 'heading', $label);
    }

    /** @param list<array{value: string|int, label: string}> $options */
    public static function select(string $name, string $label, array $options): self
    {
        return (new self($name, 'select', $label))->set('options', $options);
    }

    /** @param list<array{value: string|int, label: string}> $options */
    public static function multiselect(string $name, string $label, array $options): self
    {
        return (new self($name, 'multiselect', $label))->set('options', $options);
    }

    public function set(string $key, mixed $value): self
    {
        $this->props[$key] = $value;

        return $this;
    }

    public function required(bool $required = true): self
    {
        return $this->set('required', $required);
    }

    public function help(string $help): self
    {
        return $this->set('help', $help);
    }

    public function placeholder(string $placeholder): self
    {
        return $this->set('placeholder', $placeholder);
    }

    public function nullable(): self
    {
        return $this->set('nullable', true);
    }

    public function half(): self
    {
        return $this->set('span', 'half');
    }

    public function rows(int $rows): self
    {
        return $this->set('rows', $rows);
    }

    public function accept(string $accept): self
    {
        return $this->set('accept', $accept);
    }

    public function currencyKey(string $field): self
    {
        return $this->set('currency_field', $field);
    }

    /** Where uploads are stored: public disk by default; "protected" for paid files. */
    public function disk(string $disk, string $folder): self
    {
        return $this->set('disk', $disk)->set('folder', $folder);
    }

    public function toArray(): array
    {
        return $this->props;
    }
}
