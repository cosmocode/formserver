# Form element types

Each form element must be declared using a unique id and the element definition. All elements except hidden fields should have a label.

```
<id>:
    type: textinput
    label: Enter some data
```

The element id will be used in HTML as is, so mind the standards: no spaces, start with a letter, stick to ASCII characters and use - or _ as separators.

## Fieldsets

## Static fields

### markdown

The provided value will be rendered as Markdown.

```
<id>:
    type: markdown
    markdown: Hello _Markdown_!
```

### image

If the required `src` starts with `/` the included file is supposed to be placed directly in the document root of the app (`public`). Otherwise the link points to the form directory.

The optional `width` and `height` attributes will direct the browser to scale the image accordingly.

```
  <id>:
    type: image
    label: image label text
    src: /image.png
    width: 200
    height: 200
```

### download

If the required `href` starts with `/` the included file is supposed to be placed directly in the document root of the app (`public`). Otherwise the link points to the form directory.

```
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

```
    cols: 50
    rows: 8
```

### date

Text input that expects a date and provides a calendar picker

### time

Text input that expects a time and provides a time picker

### datetime

### email

### hidden

`value` is required

### radioset

Define available options with `choices`

```
    choices:
      - first choice
      - second choice
```

### dropdown

Define available options with `choices`

Optionally set the `default` option

Optionally enable `multiselect`

```        
    multiselect: false
    default: choose an option
    choices:
      - first choice
      - second choice
```

### checklist

Define available options with `choices`

```
    choices:
      - first choice
      - second choice
```

### email

### upload

The uploaded file will be stored with the form element's id as filename. The file extension is preserved, but the original file name is not. It will be sent as attachment if the form data is configured to be sent by email. 

### signature

This element lets a user draw a signature on screen to sign the form. If the form data is configured to be sent by email, a JPG image of the signature will be attached. Otherwise it is simply stored along with the rest of inputs  as data points to be processed as you wish.

**Note:** At this point only a single signature per form will work. 
