# Form element types

Each form element must be declared using a unique id (inside its group) and an element definition.  
Form elements can be grouped visually and/or logically in [fieldsets](#fieldsets).  
The element definition must contain at least the type of the form element.  
All form elements but the [hidden form element](#hidden) have also following basic options in the element definition.  
```yaml
<id>:
    type: <type>
    label: <label> *(optional)*
    grid: <grid> *optional*
```

The element id will be used in HTML as is, so mind the standards: no spaces, start with a letter, stick to ASCII characters and use - or _ as separators.

## fieldsets

Fieldsets can contain all types of elements, including fieldsets. The label will be displayed as a legend. 

```yaml
<id>:
    type: fieldset
    label: <label>
    children:
      <id>:
        ...
```

## Static fields

### markdown

The provided value will be rendered as Markdown. We use [PHP Markdown Extra](https://michelf.ca/projects/php-markdown/extra/), which supports some additional syntax.

```yaml
<id>:
    type: markdown
    markdown: Hello _Markdown_!
```

### image

If the required `src` starts with `/` the included file is supposed to be placed directly in the document root of the app (`public`). Otherwise the link points to the form directory.

The optional `width` and `height` attributes will direct the browser to scale the image accordingly.

```yaml
  <id>:
    type: image
    label: image label text
    src: /image.png
    width: 200
    height: 200
```

### download

If the required `href` starts with `/` the included file is supposed to be placed directly in the document root of the app (`public`). Otherwise the link points to the form directory.

```yaml
  <id>:
    type: download
    label: download label text
    href: /download.pdf
```

The linked file will be opened in a new browser tab / window.

## Dynamic fields (user input)

### textinput

Simple text input

### textarea

Multiline text input field with optional size attributes

```yaml
    width: 50%
    rows: 4
```

### date

Text input that expects a date and provides a calendar picker.

### time

Text input that expects a time and provides a time picker.

### datetime

Text input that expects a date and a time and provides a combined picker.

### email

Text input that expects a valid email (the HTML5 validation is handled by the browser).

### hidden

`value` is required

### radioset

Define available options with `choices`

```yaml
    choices:
      - first choice
      - second choice
```

### dropdown

Define available options with `choices`

Optionally set the `default` option

Optionally enable `multiselect`

```yaml
    multiselect: false
    default: choose an option
    choices:
      - first choice
      - second choice
```

### checklist

Define available options with `choices`

```yaml
    choices:
      - first choice
      - second choice
```

### upload

The uploaded file will be stored with the form element's id as filename. The file extension is preserved, but the original file name is not. It will be sent as an attachment if the form data is configured to be sent by email. 

### signature

This element lets a user draw a signature on screen to sign the form. If the form data is configured to be sent by email, a JPG image of the signature will be attached. Otherwise it is simply stored along with the rest of inputs  as data points to be processed as you wish. 
