# General form settings (meta)

```yaml
meta:
  title: 'Form title'
  css: custom.css
  logo: /logo.png

```
As with all files, the slash indicates that the file is located in `public/`, otherwise the form directory is prepended.

Your CSS file will be included after our basic styles, which are mostly plain [Bulma](https://bulma.io/).  

## Mail options

```yaml
  email:
    subject: 'Status of {{fieldset1.widget1}}'
    recipients:
      - <email>
      - <email>
    cc:
      - fieldset2.email
```

An email with user input and attached uploads will be sent to configured addresses. A copy can be sent to an address from an email field if you provide the fieldId(s) in the `cc` section.   

## Label options
There are some labels which are defined in meta, because they are repetitive or cant be set in the [form element definitions](formelements.md)

```yaml
  labels:
    button_save : <label of save button>
    button_send : <label of send button>
    uploaded_file: <label of download link>
```