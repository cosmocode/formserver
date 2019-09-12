# Input validation

User input fields can be validated using the following syntax:

```
<id>
    validation:
      <rule>: <value>
```

Currently supported rules are:

 * required: `true`
 * min: `<integer>`
 * max: `<integer>`
 * match: `<regex>`
 * filesize: `<xB|KB|MB|GB>`
 * fileext: `<case-insensitive comma-separated list of file extensions>`
 
Example for a text input:
 
 ```
    validation:
      match: /^exact match$/
```

Example for an upload:

```
    validation:
      required: true
      filesize: 2MB
      fileext: jpg, pdf, PNG
```

If the input is not required, no further validation rules will be applied.
