# Form element types

Each form element must be declared using a unique id (inside its group) and an element definition.  
The id will be used in HTML as is, so mind the standards: no spaces, start with a letter, stick to ASCII characters and use - or _ as separators.  
Form elements can be grouped visually and/or logically in [fieldsets](#fieldsets).  
The element definition must contain at least the type of the form element.  
Options:  
* `label` _(optional)_ - the label of the form element (excluded: [hidden](#hidden), [markdown](#markdown))
* `labelsmall` _(optional)_ - if set to true, the label will be rendered in regular font instead of default bold
* `tooltip` _(optional)_ - Shows a hint for the element. Ignored in structure elements like spacers and HR. Also see [Tooltip styling](meta.md#Tooltips)
* `modal` _(optional)_ - Content of a simple Bulma modal in markdown syntax. Refer to the bundled EXAMPLE configuration. Images are interpreted as files located directly in your form directory. The option is ignored in structure elements like spacers and HR.  
* `column` _(optional)_ - [bulma column sizes](https://bulma.io/documentation/columns/sizes/) defining the width of the form element (excluded: [hidden](#hidden)). You can use [offset](https://bulma.io/documentation/columns/sizes/#offset) to position the columns, for example `is-half is-offset-one-quarter` to center a half-width column.
```yaml
<id>:
    type: <type>
    label: <label>
    column: <column>
```


## Fieldsets

Fieldsets group other form elements (including nested fieldsets).  

Options:
* `children` _(required)_ - containing child form elements
* `background` _(optional)_ the background color may be one of [https://bulma.io/documentation/helpers/color-helpers/#background-color](Bulma's colors) (without the prefix `has-background-`) or a valid CSS definition.
* `tablestyle` _(optional)_ set table view true or false. This will order the contained fieldsets as if they were table columns. The columns will have alternating colors ("zebra"). The head column will be populated by the labels of the children of the first fieldset. Labels in other columns will be hidden to avoid duplication. If cells in the first row must be skipped then [Spacers](#Spacer) can be used.
* `scrollable` _(optional)_ can be used with `tablestyle`: The fieldset will not responsively adapt to screen width, but will extend horizontally and can be scrolled. The option may have unpredictable effects in complex forms!
* `toggle` _(optional)_ - the fieldset is disabled and hidden until the toggle condition is met
  * `field` - dotted path to the field whose value will be evaluated to match the toggle condition
  * `value` - required value to toggle the fieldset on, can be a simple string or a YAML array

```yaml
<id>:
    type: fieldset
    toggle:
      field: <fieldset1>.<fieldset2>.<text1>
      value: 'toogle value'
    children:
      text1:
        type: textinput
      ...
```

## Static fields

### Markdown

The provided value will be rendered as Markdown. We use [PHP Markdown Extra](https://michelf.ca/projects/php-markdown/extra/), which supports some additional syntax.  

Options:
* `markdown` _(required)_ - markdown which will be transformed to html
```yaml
<id>:
    type: markdown
    markdown: Hello _Markdown_!
```

### Image

Representation of an image.  

Options:
* `width` _(optional)_ - sets the width of the image
* `height` _(optional)_ - sets the height of the image
* `src` _(required)_ - points to the image file

If the required `src` starts with
* `/` the included file is supposed to be placed directly in the document root of the app (`public`)
* 'http' or 'https' the value will be interpreted as a url
* otherwise the link points to the form directory (`data/<form direcotry>/`).

```yaml
  <id>:
    type: image
    label: image label text
    src: /image.png
    width: 200
    height: 200
```

### Download

Representation of a link.  

Options:
* `href` _(required)_ - points to the file to download

If the required `href` starts with
* `/` the included file is supposed to be placed directly in the document root of the app (`public`)
* `http` or `https` the value will be interpreted as a url
* otherwise the link points to the form directory (`data/<form directory>/`).

```yaml
  <id>:
    type: download
    label: download label text
    href: /download.pdf
```

The linked file will be opened in a new browser tab / window.

### Hidden

Representation of a hidden input.  

Options:
* `value` _(required)_ - value of the hidden input

```yaml
  <id>:
    type: hidden
    value: "hidden value"
```


### Spacer

Representation of a empty table cell. Should be used in table fieldset (tableStyle: true).
Options:
* `label` _(required)_ - The label. If the spacer is inside the first fieldset of a table fieldset (tableStyle: true) then the label will be used
* `double` _(optional)_ - If set to true also skips cell below

```yaml
  <id>:
    type: spacer
    label: "label"
    double: true
```

### Horizontal line
Representation of a hr.

Options:
* `column` _(optional)_
* `color` _(optional)_ - The color of the hr
* `height` _(optional)_ - The height of the hr

```yaml
  <id>:
    type: hr
    column: is-full
    color: '#f5f5f5'
    height: 2
```

## Dynamic fields (user input)

All dynamic fields have a value the user can enter.
They are required by default.  

Options:
* `validation` _(optional)_ - used to apply [validation](validation.md)
* `readonly` _(optional)_ - Disables user input but keeps any pre-filled values intact. Fields where users type in values get an actual HTML attribute `readonly`. Select fields like dropdowns and radios, where this property is not allowed, are visually toned down and are non-interactive. Useful when values are pre-filled from `values.yaml`

```yaml
  <id>:
    type: <type>
    tooltip: 'A useful hint for this field.'
    readonly: true
    validation:
      required: false
      match: /^regex_expression$/
```

### Textinput

Simple text input.

```yaml
  <id>:
    type: textinput
    label: text label
    column: is-half
    placeholder: placeholder value for input
    validation:
      required: false
```

* `clone` _(optional)_ - When set to `true`, adds a clone button to the field. That way you can repeat the same input to create a list of variable length.

### Numberinput

Simple number (integer) input.

```yaml
  <id>:
    type: numberinput
    label: numberinput label
    column: is-half
    placeholder: placeholder value for input
    validation:
      required: false
```

* `clone` _(optional)_ - When set to `true`, adds a clone button to the field. That way you can repeat the same input to create a list of variable length.

### Textarea

Multiline text input field with optional size attributes.  

Options:
* `rows` _(optional)_ - sets the number of rows
* `cols` _(optional)_ - sets the number of cols (usage of [column](https://bulma.io/documentation/columns/sizes/) should be prefered)

```yaml
  <id>:
    type: textarea
    label: textarea label
    column: is-half
    placeholder: placeholder value for input
    validation:
      required: false
    rows: 7
    cols: 40
```

### Date

Text input that expects a date and provides a calendar picker.

```yaml
  <id>:
    type: date
    label: date label
    column: is-two-thirds
    placeholder: placeholder value for input
    validation:
      required: false
```

Options:
* `clone` _(optional)_ - When set to `true`, adds a clone button to the field. That way you can repeat the same input to create a list of variable length.

### Time

Text input that expects a time and provides a time picker.

```yaml
  <id>:
    type: time
    label: time label
    column: is-two-thirds
    placeholder: placeholder value for input
    validation:
      required: false
```
Options:
* `clone` _(optional)_ - When set to `true`, adds a clone button to the field. That way you can repeat the same input to create a list of variable length.

### Datetime

Text input that expects a date and a time and provides a combined picker.

```yaml
  <id>:
    type: datetime
    label: datetime label
    column: is-two-thirds
    placeholder: placeholder value for input
    validation:
      required: false
```
Options:
* `clone` _(optional)_ - When set to `true`, adds a clone button to the field. That way you can repeat the same input to create a list of variable length.

### Email

Text input that expects a valid email (the HTML5 validation is handled by the browser).

```yaml
  <id>:
    type: email
    label: email label
    column: is-half
    placeholder: placeholder value for input
    validation:
      required: false
```

Options:
    * `clone` _(optional)_ - When set to `true`, adds a clone button to the field. That way you can repeat the same input to create a list of variable length.

### Radioset

Representation of a radio group.  

Options:
* `alignment` _(optional)_ - sets the alignment of the radios, possible values are `vertical` or `horizontal` (default)
* `choices` _(required)_ - defines available options/radios

```yaml
  <id>:
    type: radioset
    label: radioset label
    alignment: vertical
    validation:
      required: true
    choices:
      - First choice
      - Second choice
      - Third choice
```

### Dropdown

Representation of a select input.  

Options:
* **EITHER** `choices` _(optionally required)_ - defines available options. Markdown is supported.
*  **OR** `conditional_choices` _(optionally required)_ - defines options available if a condition is met (depending on the value of another form field). This option has priority over `choices`!
* `empty_label` _(optional)_ - a placeholder text shown if no value was chosen (e.g. "Please select"). **Note:** this is not a real option and has no value that could be saved.
* `multiselect` _(optional)_ - enables selecting multiple options
* `size` _(optional)_ - if multiselect is turned on this defines the number of rows shown, otherwise ignored
* `default` _(optional)_ : Preselects a choice. This is only triggered if the form was never saved before. **Preselect in toggles are not supported yet.**

```yaml
  <id>:
    type: dropdown
    label: dropdown label
    multiselect: true
    size: 3
    empty_label: choose an option
    default: 'first choice [a nice link](https://www.cosmocode.de)'
    choices:
      - first choice [a nice link](https://www.cosmocode.de)
      - second choice
```

```yaml
    conditional_choices:
      -
        field: fieldsetLevel1.FieldsetLevel2.myFieldname
        value: 'Value A'
        choices:
          - A1
          - A2
      - field: fieldsetLevel1.FieldsetLevel2.myFieldname
        value: 'Value B'
        choices:
          - 10 B
          - 20 B
          - 30 B
```

### Checklist

Representation of a checkbox group.

Options:
* `alignment` _(optional)_ - sets the alignment of the checkboxes, possible values are `vertical` or `horizontal` (default)
* `choices` _(required)_ - defines available options/checkboxes. Markdown is supported.
* `default` _(optional)_ : Preselects a choice. This is just triggered if the form was never saved before. **Preselect in toggles are not supported yet.**
```yaml
  <id>:
    type: checklist
    label: checklist label
    alignment: vertical
    validation:
      required: false
    default: 'First choice'
    choices:
      - First choice
      - Second choice
      - Third choice
```

### Upload

The uploaded file will be stored with the form element's id as filename. The file extension is preserved, but the original file name is not. It will be sent as an attachment if the form data is configured to be sent by email.  

Options:
* `validation` _(optional)_ - the upload form element has special validation rules
  * `filesize` - max upload size, example values: `1048576B`, `1024KB`, `1MB` 
  * `fileext` - allowed file extensions, example value (will be lower cased automatically): `jpg, pdf, txt`

```yaml
  <id>:
    type: upload
    label: upload label
    validation:
      filesize: 2MB
      fileext: jpg, pdf, txt
```

### Signature

This element lets a user draw a signature on screen to sign the form. If the form data is configured to be sent by email, a JPG image of the signature will be attached. Otherwise it is simply stored along with the rest of inputs  as data points to be processed as you wish.  

Options:
* `width` _(optional)_ - sets the width of the signature field
* `height` _(optional)_ - sets the height of the signature field

```yaml
  <id>:
    type: signature
    label: signature label
    height: 400
    width: 900
    validation:
      required: true
```
