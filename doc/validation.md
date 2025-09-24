# Input validation

User input fields can be validated using the following syntax:

```
<id>
    validation:
      <rule>: <value>
```

Currently supported rules are:

 * required: `false` (by default all fields are required)
 * min: `<integer>`
 * max: `<integer>`
 * match: `<regex>` (for text input)
 * maxlength: `<integer>` (for text inputs and textareas)
 * start: `<date|time|datetime>` (for date, time, and datetime inputs)
 * end: `<date|time|datetime>` (for date, time, and datetime inputs)
 * filesize: `<xB|KB|MB|GB>`
 * fileext: `<case-insensitive comma-separated list of file extensions>`
 
Example for a text input:
 
 ```
    validation:
      match: /^exact match$/
      maxlength: 50
```

Example for an upload:

```
    validation:
      required: true
      filesize: 2MB
      fileext: jpg, pdf, PNG
```

Example for date validation:

```
    validation:
      start: '2023-01-01'
      end: '2025-12-31'
```

Example for datetime validation:

```
    validation:
      start: '2023-01-01 09:00'
      end: '2025-12-31 17:00'
```

Example for time validation:

```
    validation:
      start: '09:00'
      end: '17:00'
```
