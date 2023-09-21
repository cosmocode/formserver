# YAML reference

## App configuration

[App settings](app.md) offer a few options to adjust the defaults, including mail server settings and the messages displayed to the user, including a general error message.

## Form configuration

The form description must be placed in a subdirectory of `data` in a file named `config.yaml`. The file must contain two sections: `meta` and `form`

```yaml
meta:
  title: <headline>
form:
  <formElementDefinitions>
```

### General options

See [form settings](meta.md) and the [config example](../data/EXAMPLE/config.yaml) for customization and mailing options.

### Form element types

There are static and dynamic form elements. The static ones just display text, images or download links. The dynamic ones are fields that take user input.

See [element definitions](formelements.md) for configuration details.

### Input validation

See [details](validation.md).
