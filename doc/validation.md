# Input validation

User input fields can be validated using the following syntax:

```
<id>
    validation:
      <rule>: <value>
```

We use [Respect Validation](https://github.com/Respect/Validation) under the hood.

Currently supported rules are:

 * required: `false` (by default all fields are required)
 * min: `<integer>`
 * max: `<integer>`
 * match: `<regex>`
 * filesize: `<xB|KB|MB|GB>`
 * fileext: `<case-insensitive comma-separated list of file extensions>`
 
Example for a text or textarea input:
 
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
