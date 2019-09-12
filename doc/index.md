# YAML reference

## App configuration

 * settings
 * language

## Form configuration

The form description must be placed in a subdirectory of `data` in a file named `config.yaml`. The file must contain two sections: `meta` and `form`

```
meta:
  title: <headline>
form:
  <formElementDefinitions>
```

### General options

See [form settings](meta.md) for customization and mailing options.

### Form element types

There are static and dynamic form elements. The static ones just display text, images or download links. The dynamic ones are fields that take user input.

See [element definitions](formelements.md) for configuration details.

### Input validation


