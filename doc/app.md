# App settings

All settings and message strings are defined in `conf/`

You can override the defaults by copying `settings.default.yaml` and/or `language.default.yaml` to `*.local.yaml` and adjusting the values. Also see [Language Settings](meta.md#Translations)

## Error messages

If a form cannot be found, a customized message can be shown to the user. There are two global app settings to define the message:

1. `errorPageNotFound`: path to static HTML file. The default value is `data/404.html` and a very minimalist file is provided in this location. Override this with path to your own file. `data` is the recommended location, but you can also put it in `public`. Leave empty not to use the HTML page. 
2. `errorMessageNotFound`: simple text message, displayed when the error page is not found.

In case of other global errors, you can customize the HTML or the text message as well by adjusting `errorPageGeneral` and `errorMessageGeneral`.